<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController as TransactionApiController;
use App\Http\Controllers\Api\BudgetController as BudgetApiController;
use App\Http\Controllers\Api\CategoryController as CategoryApiController;
use App\Http\Controllers\Api\SavingsGoalController as SavingsGoalApiController;
use App\Http\Controllers\Api\ReportController;

// Sve API rute su pod /api/v1 i imena kreću sa api.*
Route::prefix('v1')->name('api.')->group(function () {

    // --- PUBLIC (bez tokena) ---
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login',    [AuthController::class, 'login'])
        ->middleware('throttle:login') 
        ->name('auth.login');

        
        Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])
            ->name('auth.forgot');

        Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])
            ->name('auth.reset');

    // --- PROTECTED (zahteva Bearer token / Sanctum) ---
   Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        Route::get('transactions/export', [\App\Http\Controllers\Api\TransactionController::class, 'export'])
            ->name('transactions.export');

            Route::get('transactions/export.csv', [\App\Http\Controllers\Api\TransactionController::class, 'export'])
            ->name('transactions.export.csv');

            Route::get('transactions/export.pdf', [\App\Http\Controllers\Api\TransactionController::class, 'export'])->name('transactions.export.pdf');

        // auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // REST resource rute (JSON)
        Route::apiResource('transactions',  TransactionApiController::class);
        Route::apiResource('budgets',       BudgetApiController::class);
        Route::apiResource('categories',    CategoryApiController::class);
        Route::apiResource('savings-goals', SavingsGoalApiController::class)->names('savings_goals');

        //  3 različite dodatne API rute
        Route::get('transactions/summary', [ReportController::class, 'transactionsSummary'])
            ->name('transactions.summary');

        Route::get('categories/{category}/transactions', [TransactionApiController::class, 'byCategory'])
            ->name('categories.transactions');

        Route::post('transactions/bulk', [TransactionApiController::class, 'bulkStore'])
            ->name('transactions.bulk');

      
      
    });
});
