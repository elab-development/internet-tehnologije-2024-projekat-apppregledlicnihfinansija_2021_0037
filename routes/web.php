<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\TransactionController;








Route::get('/', function () {
    return view('welcome');
})->name('home');




    Route::resource('budgets', BudgetController::class);
    Route::resource('savings_goals', SavingsGoalController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('transactions', TransactionController::class);
  
   

