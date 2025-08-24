<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingsGoal;
use App\Models\Alert;

class AdminController extends Controller
{
    public function stats(Request $request)
    {
        // Middleware 'role:admin' već štiti ovu rutu
        return response()->json([
            'users'        => User::count(),
            'transactions' => Transaction::count(),
            'budgets'      => Budget::count(),
            'categories'   => Category::count(),
            'goals'        => SavingsGoal::count(),
            'alerts'       => Alert::count(),
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
