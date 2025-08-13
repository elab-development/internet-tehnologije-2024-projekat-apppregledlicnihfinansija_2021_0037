<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TransactionController as TransactionApiController;
use App\Http\Controllers\Api\BudgetController as BudgetApiController;
use App\Http\Controllers\Api\CategoryController as CategoryApiController;
use App\Http\Controllers\Api\SavingsGoalController as SavingsGoalApiController;
use App\Http\Controllers\Api\ReportController;

// Sve API rute (JSON), opcioni prefiks v1 i imena 'api.*'
Route::middleware('auth:sanctum')
    ->prefix('v1')
    ->name('api.')
    ->group(function () {
        // REST resource rute
        Route::apiResource('transactions', TransactionApiController::class);
        Route::apiResource('budgets',      BudgetApiController::class);
        Route::apiResource('categories',   CategoryApiController::class);
        Route::apiResource('savings-goals', SavingsGoalApiController::class)->names('savings_goals');

        // + 3 različite API rute (van resource-a) — primeri:

        // 1) Agregat po periodu (GET)
        Route::get('transactions/summary', [ReportController::class, 'transactionsSummary'])
            ->name('transactions.summary');

        // 2) Ugnježdena lista: sve transakcije za datu kategoriju (GET)
        Route::get('categories/{category}/transactions', [TransactionApiController::class, 'byCategory'])
            ->name('categories.transactions');

        // 3) Bulk kreiranje transakcija (POST)
        Route::post('transactions/bulk', [TransactionApiController::class, 'bulkStore'])
            ->name('transactions.bulk');
    });
