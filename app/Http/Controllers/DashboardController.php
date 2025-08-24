<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\SavingsGoal;

class DashboardController extends Controller
{
    public function index()
    {
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');
        $latestTransactions = Transaction::latest()->take(5)->get();
        $savingsGoals = SavingsGoal::all();

        return view('dashboard', compact(
            'totalIncome',
            'totalExpense',
            'latestTransactions',
            'savingsGoals'
        ));
    }
}
