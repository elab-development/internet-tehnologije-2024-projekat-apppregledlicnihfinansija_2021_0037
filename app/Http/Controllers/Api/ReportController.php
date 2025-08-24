<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Helper da ne dobijemo 500 ako nema user-a
    private function userIdOr401(Request $request): int
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        return (int) $user->id;
    }

    // GET /api/v1/transactions/summary?from=YYYY-MM-DD&to=YYYY-MM-DD
    public function transactionsSummary(Request $request)
    {
        $userId = $this->userIdOr401($request);

        $from = $request->get('from') ?? now()->startOfMonth()->toDateString();
        $to   = $request->get('to')   ?? now()->endOfMonth()->toDateString();

        $rows = DB::table('transactions')
            ->select('type', DB::raw('SUM(amount) as total'))
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->groupBy('type')
            ->pluck('total','type');

        return response()->json([
            'from'    => $from,
            'to'      => $to,
            'income'  => (float) ($rows['income']  ?? 0),
            'expense' => (float) ($rows['expense'] ?? 0),
            'net'     => (float) ($rows['income'] ?? 0) - (float) ($rows['expense'] ?? 0),
        ]);
    }

    // GET /api/v1/reports/monthly
    public function monthlyTotals(Request $request)
    {
        $userId = $this->userIdOr401($request);

        $end   = Carbon::now()->endOfMonth();
        $start = (clone $end)->subMonths(11)->startOfMonth();

        $cacheKey = "rep:monthly:{$userId}:{$start->toDateString()}-{$end->toDateString()}";

        $rows = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $start, $end) {
            return DB::table('transactions')
                ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym")
                ->selectRaw("SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) as income")
                ->selectRaw("SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense")
                ->where('user_id', $userId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('ym')
                ->orderBy('ym')
                ->get();
        });

        // popuni prazne mesece nulama
        $cursor = (clone $start);
        $series = [];
        while ($cursor <= $end) {
            $ym = $cursor->format('Y-m');
            $found = $rows->firstWhere('ym', $ym);
            $income  = (float)($found->income  ?? 0);
            $expense = (float)($found->expense ?? 0);
            $series[] = [
                'ym'      => $ym,
                'income'  => $income,
                'expense' => $expense,
                'net'     => $income - $expense,
            ];
            $cursor->addMonth();
        }

        return response()->json([
            'start' => $start->toDateString(),
            'end'   => $end->toDateString(),
            'data'  => $series,
        ]);
    }

    // GET /api/v1/reports/categories?year=YYYY&month=M
    public function categoryBreakdown(Request $request)
    {
        $userId = $this->userIdOr401($request);

        $year  = (int)($request->query('year')  ?: Carbon::now()->year);
        $month = (int)($request->query('month') ?: Carbon::now()->month);

        $cacheKey = "rep:cat:{$userId}:{$year}-{$month}";
        $rows = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $year, $month) {
            return DB::table('transactions as t')
                ->leftJoin('categories as c', 'c.id', '=', 't.category_id')
                ->where('t.user_id', $userId)
                ->where('t.type', 'expense')
                ->whereYear('t.date', $year)
                ->whereMonth('t.date', $month)
                ->groupBy('t.category_id', 'c.name')
                ->orderByDesc(DB::raw('SUM(t.amount)'))
                ->get([
                    DB::raw('COALESCE(c.name, "Ostalo") as name'),
                    DB::raw('COALESCE(SUM(t.amount),0) as total'),
                ]);
        });

        return response()->json([
            'year'  => $year,
            'month' => $month,
            'data'  => $rows->map(fn($r) => ['name' => $r->name, 'total' => (float)$r->total])->values(),
        ]);
    }
}
