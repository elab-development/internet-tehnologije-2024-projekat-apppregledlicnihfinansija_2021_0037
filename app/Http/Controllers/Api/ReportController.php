<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // GET /api/v1/transactions/summary?from=YYYY-MM-DD&to=YYYY-MM-DD
    public function transactionsSummary(Request $request)
    {
        $userId = $request->user()->id;

        $from = $request->get('from') ?? now()->startOfMonth()->toDateString();
        $to   = $request->get('to')   ?? now()->endOfMonth()->toDateString();

        $rows = DB::table('transactions')
            ->select('type', DB::raw('SUM(amount) as total'))
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->groupBy('type')
            ->pluck('total','type');

        $income  = (float) ($rows['income']  ?? 0);
        $expense = (float) ($rows['expense'] ?? 0);

        return response()->json([
            'from'    => $from,
            'to'      => $to,
            'income'  => $income,
            'expense' => $expense,
            'net'     => $income - $expense,
        ]);
    }
}
