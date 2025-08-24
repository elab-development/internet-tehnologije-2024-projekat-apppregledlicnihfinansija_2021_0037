<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ExchangeController extends Controller
{
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

        // Provider 1: exchangerate.host
        try {
            $r1 = Http::timeout(8)->retry(2, 300)
                ->acceptJson()
                ->get('https://api.exchangerate.host/convert', [
                    'from'   => $from,
                    'to'     => $to,
                    'amount' => $amount,
                ]);

            if (!$r1->successful()) {
                Log::warning('exchangerate.host not successful', [
                    'status' => $r1->status(),
                    'body'   => $r1->body(),
                ]);
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
            Log::warning('exchangerate.host exception', ['msg' => $e->getMessage()]);
        }

        // Provider 2 (fallback): frankfurter.app
        try {
            $r2 = Http::timeout(8)->retry(2, 300)
                ->acceptJson()
                ->get('https://api.frankfurter.app/latest', [
                    'amount' => $amount,
                    'from'   => $from,
                    'to'     => $to,
                ]);

            if (!$r2->successful()) {
                Log::warning('frankfurter.app not successful', [
                    'status' => $r2->status(),
                    'body'   => $r2->body(),
                ]);
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
            Log::warning('frankfurter.app exception', ['msg' => $e->getMessage()]);
        }

        // FINAL FALLBACK: native streams (radi i kad cURL/CA bundle prave problem)
        try {
            $url = 'https://api.frankfurter.app/latest?' . http_build_query([
                'amount' => $amount,
                'from'   => $from,
                'to'     => $to,
            ]);

            $json = @file_get_contents($url);
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
                Log::warning('streams frankfurter: no rate field', ['body' => $d]);
            } else {
                Log::warning('streams frankfurter: file_get_contents failed');
            }
        } catch (\Throwable $e) {
            Log::warning('streams frankfurter exception', ['msg' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Exchange service unavailable'], 503);
    }
}
