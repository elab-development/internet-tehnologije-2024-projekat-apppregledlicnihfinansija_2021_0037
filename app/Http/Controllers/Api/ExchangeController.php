<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ExchangeController extends Controller
{
    /**
     * HTTP helper sa opcionalnim isključenjem SSL verifikacije (lokalni dev).
     */
    private function http()
    {
        $client = Http::timeout(8)->retry(2, 300)->acceptJson()
            ->withHeaders(['User-Agent' => 'LaravelFX/1.0']);
        if (config('services.http_insecure', false)) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }

    /**
     * Konverzija iznosa.
     */
    public function convert(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required','numeric','min:0'],
            'from'   => ['required','string','size:3'],
            'to'     => ['required','string','size:3'],
        ]);

        $from   = strtoupper($data['from']);
        $to     = strtoupper($data['to']);
        $amount = (float) $data['amount'];

        // 1) exchangerate.host (podržava RSD)
        try {
            $r1 = $this->http()->get('https://api.exchangerate.host/convert', [
                'from' => $from, 'to' => $to, 'amount' => $amount,
            ]);
            if (!$r1->successful()) {
                Log::warning('exchangerate.host not successful', ['status'=>$r1->status(), 'body'=>$r1->body()]);
            }
            if ($r1->ok() && isset($r1['result'])) {
                return response()->json([
                    'provider' => 'exchangerate.host',
                    'amount'   => $amount,
                    'from'     => $from,
                    'to'       => $to,
                    'rate'     => $r1['info']['rate'] ?? null,
                    'result'   => $r1['result'],
                    'date'     => $r1['date'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('exchangerate.host exception', ['msg'=>$e->getMessage()]);
        }

        // 2) open.er-api.com (v6) — base = FROM, rates[TO]
        try {
            $r3 = $this->http()->get("https://open.er-api.com/v6/latest/{$from}");
            if ($r3->ok() && isset($r3['rates'][$to])) {
                $rate   = (float) $r3['rates'][$to];
                $result = $amount * $rate;
                return response()->json([
                    'provider' => 'open.er-api.com',
                    'amount'   => $amount,
                    'from'     => $from,
                    'to'       => $to,
                    'rate'     => $rate,
                    'result'   => $result,
                    'date'     => $r3['time_last_update_utc'] ?? Carbon::today()->toDateString(),
                ]);
            } else {
                Log::warning('er-api no rate', ['base'=>$from, 'to'=>$to, 'body'=>$r3->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('er-api exception', ['msg'=>$e->getMessage()]);
        }

        // 3) frankfurter.app (ECB — često nema RSD, ali probamo)
        try {
            $r2 = $this->http()->get('https://api.frankfurter.app/latest', [
                'amount' => $amount, 'from' => $from, 'to' => $to,
            ]);
            if (!$r2->successful()) {
                Log::warning('frankfurter.app not successful', ['status'=>$r2->status(), 'body'=>$r2->body()]);
            }
            if ($r2->ok() && isset($r2['rates'][$to])) {
                $result = (float) $r2['rates'][$to];
                $rate   = $amount > 0 ? $result / $amount : null;
                return response()->json([
                    'provider' => 'frankfurter.app',
                    'amount'   => $amount,
                    'from'     => $from,
                    'to'       => $to,
                    'rate'     => $rate,
                    'result'   => $result,
                    'date'     => $r2['date'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('frankfurter.app exception', ['msg'=>$e->getMessage()]);
        }

        // 4) FINAL FALLBACK: native streams (sa SSL kontekstom)
        try {
            $url = 'https://api.frankfurter.app/latest?' . http_build_query([
                'amount' => $amount, 'from' => $from, 'to' => $to,
            ]);

            $ctx = stream_context_create([
                'http' => ['header' => "User-Agent: LaravelFX/1.0\r\n"],
                'ssl'  => [
                    'verify_peer'      => !config('services.http_insecure', false),
                    'verify_peer_name' => !config('services.http_insecure', false),
                ],
            ]);
            $json = @file_get_contents($url, false, $ctx);
            if ($json !== false) {
                $d = json_decode($json, true);
                if (isset($d['rates'][$to])) {
                    $result = (float) $d['rates'][$to];
                    $rate   = $amount > 0 ? $result / $amount : null;
                    return response()->json([
                        'provider' => 'frankfurter.app (streams)',
                        'amount'   => $amount,
                        'from'     => $from,
                        'to'       => $to,
                        'rate'     => $rate,
                        'result'   => $result,
                        'date'     => $d['date'] ?? null,
                    ]);
                }
                Log::warning('streams frankfurter: no rate field', ['body'=>$d]);
            } else {
                Log::warning('streams frankfurter: file_get_contents failed');
            }
        } catch (\Throwable $e) {
            Log::warning('streams frankfurter exception', ['msg'=>$e->getMessage()]);
        }

        return response()->json(['message' => 'Exchange service unavailable'], 503);
    }

    /**
     * Samo kurs (1 FROM -> TO), keš 30 min.
     */
    public function rate(Request $request)
    {
        $data = $request->validate([
            'from' => ['required','string','size:3'],
            'to'   => ['required','string','size:3'],
        ]);

        $from = strtoupper($data['from']);
        $to   = strtoupper($data['to']);

        if ($from === $to) {
            return response()->json([
                'from'=>$from,'to'=>$to,'rate'=>1.0,'provider'=>'local',
                'date'=>Carbon::today()->toDateString(),'cached'=>false,
            ]);
        }

        $cacheKey = sprintf('fx-rate:%s:%s:%s', $from, $to, Carbon::today()->toDateString());

        $payload = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($from, $to) {
            // 1) exchangerate.host (amount=1)
            try {
                $r1 = $this->http()->get('https://api.exchangerate.host/convert', [
                    'from'=>$from,'to'=>$to,'amount'=>1,
                ]);
                if ($r1->ok() && isset($r1['result'])) {
                    return [
                        'provider'=>'exchangerate.host',
                        'rate'    => (float)$r1['result'],
                        'date'    => $r1['date'] ?? Carbon::today()->toDateString(),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('exchangerate.host rate exception', ['msg'=>$e->getMessage()]);
            }

            // 2) open.er-api.com (base=FROM)
            try {
                $r3 = $this->http()->get("https://open.er-api.com/v6/latest/{$from}");
                if ($r3->ok() && isset($r3['rates'][$to])) {
                    return [
                        'provider'=>'open.er-api.com',
                        'rate'    => (float)$r3['rates'][$to],
                        'date'    => $r3['time_last_update_utc'] ?? Carbon::today()->toDateString(),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('er-api rate exception', ['msg'=>$e->getMessage()]);
            }

            // 3) frankfurter (ECB)
            try {
                $r2 = $this->http()->get('https://api.frankfurter.app/latest', [
                    'from'=>$from,'to'=>$to,
                ]);
                if ($r2->ok() && isset($r2['rates'][$to])) {
                    return [
                        'provider'=>'frankfurter.app',
                        'rate'    => (float)$r2['rates'][$to],
                        'date'    => $r2['date'] ?? Carbon::today()->toDateString(),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('frankfurter.app rate exception', ['msg'=>$e->getMessage()]);
            }

            return null;
        });

        if (!$payload) {
            return response()->json(['message' => 'Exchange rate unavailable'], 503);
        }

        return response()->json($payload + ['from'=>$from,'to'=>$to,'cached'=>true]);
    }

    /**
     * Lista valuta (keš 1 dan)
     */
    public function currencies()
    {
        $payload = Cache::remember('fx-currencies', now()->addDay(), function () {
            // 1) frankfurter (jednostavno)
            try {
                $res = $this->http()->get('https://api.frankfurter.app/currencies');
                if ($res->ok() && is_array($res->json())) {
                    return $res->json();
                }
            } catch (\Throwable $e) {
                Log::warning('currencies frankfurter exception', ['msg'=>$e->getMessage()]);
            }

            // 2) exchangerate.host /symbols
            try {
                $res = $this->http()->get('https://api.exchangerate.host/symbols');
                $json = $res->json();
                if ($res->ok() && isset($json['symbols']) && is_array($json['symbols'])) {
                    $out = [];
                    foreach ($json['symbols'] as $code => $info) {
                        $out[$code] = $info['description'] ?? $info['code'] ?? $code;
                    }
                    return $out;
                }
            } catch (\Throwable $e) {
                Log::warning('currencies exchangerate.host exception', ['msg'=>$e->getMessage()]);
            }

            // 3) minimalni fallback
            return [
                'RSD' => 'Serbian Dinar',
                'EUR' => 'Euro',
                'USD' => 'US Dollar',
                'GBP' => 'British Pound',
                'CHF' => 'Swiss Franc',
            ];
        });

        $list = [];
        foreach ($payload as $code => $name) {
            $list[] = ['code' => strtoupper($code), 'name' => $name];
        }
        return response()->json($list);
    }
}
