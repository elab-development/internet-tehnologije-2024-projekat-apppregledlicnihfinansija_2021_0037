<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\PointsLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Gamification
{
    protected static function isPremium(User $user): bool
    {
        if (method_exists($user, 'hasRole')) {
            return (bool) $user->hasRole('premium');
        }
        return ($user->role ?? null) === 'premium';
    }

    public static function award(User $user, int|float $points, string $reason, array $meta = []): void
    {
        $p = (int) round($points);
        if ($p === 0) return;

        // (opciono) premijum-only gate: ako ti treba, odkomentariši:
        // if (!self::isPremium($user)) return;

        // Multiplikator (po želji promeni)
        $mult = self::isPremium($user) ? 1.5 : 1.0;

        // Cap po dodeli (anti-spam)
        $cap   = 50;
        $final = (int) round(min($cap, max(0, $p)) * $mult);
        if ($final <= 0) return;

        DB::transaction(function () use ($user, $final, $reason, $meta) {
            // Ako postoji kolona users.points, uvećaj je (ako ne postoji, preskoči — neće puknuti)
            if (Schema::hasColumn('users', 'points')) {
                // ako je NULL, postavi na 0 da increment ne sabira preko NULL
                DB::table('users')->where('id', $user->id)->whereNull('points')->update(['points' => 0]);
                $user->increment('points', $final);
            }

            // Uvek upiši log (ako tablica postoji; ako je nema, ovo bi puklo — zato proverimo)
            if (Schema::hasTable('points_logs')) {
                PointsLog::create([
                    'user_id' => $user->id,
                    'points'  => $final,
                    'reason'  => $reason,
                    'meta'    => $meta,
                ]);
            }
        });
    }

    public static function alert(User $user, string $type, string $title, ?string $message = null, array $meta = []): void
    {
        // Portabilna deduplikacija BEZ JSON upita (ne zavisi od verzije MySQL/SQLite)
        $dedupe = $meta['dedupe'] ?? null;

        if ($dedupe && Schema::hasTable('alerts')) {
            $exists = Alert::where('user_id', $user->id)
                ->where('type', $type)
                ->latest('id')
                ->take(50)
                ->get(['id', 'meta'])
                ->contains(function ($a) use ($dedupe) {
                    $m = $a->meta ?? [];
                    return is_array($m) && (($m['dedupe'] ?? null) === $dedupe);
                });

            if ($exists) {
                return;
            }
        }

        if (Schema::hasTable('alerts')) {
            Alert::create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'meta'    => $meta,
            ]);
        }
    }

    public static function total(User $user): int
    {
        // Ako postoji kolona points, koristi je (instant); fallback na SUM iz logs.
        if (Schema::hasColumn('users', 'points')) {
            return (int) ($user->points ?? 0);
        }
        if (Schema::hasTable('points_logs')) {
            return (int) PointsLog::where('user_id', $user->id)->sum('points');
        }
        return 0;
    }

    public static function levelFor(int $points): int
    {
        $step = 250;
        return 1 + intdiv(max(0, $points), max(1, $step));
    }
}
