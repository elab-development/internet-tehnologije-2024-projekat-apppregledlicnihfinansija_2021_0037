<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AccountController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController as TransactionApiController;
use App\Http\Controllers\Api\BudgetController as BudgetApiController;
use App\Http\Controllers\Api\CategoryController as CategoryApiController;
use App\Http\Controllers\Api\SavingsGoalController as SavingsGoalApiController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ExchangeController;
use App\Http\Controllers\Api\AdminController;




// Sve API rute su pod /api/v1 i imena kreću sa api.*
Route::prefix('v1')->name('api.')->group(function () {

    // --- PUBLIC (bez tokena) ---
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('auth.login');


    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot');

    Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])
        ->name('auth.reset');

    // --- PROTECTED (zahteva Bearer token / Sanctum) ---
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        Route::get('user', [AccountController::class, 'me'])->name('user'); // ako već nemaš svoj
        Route::post('account/upgrade', [AccountController::class, 'upgrade'])->name('account.upgrade');

       Route::get('admin/stats', [\App\Http\Controllers\Api\AdminController::class, 'stats'])
    ->middleware('role:admin')
    ->name('admin.stats');

        Route::get('transactions/export', [\App\Http\Controllers\Api\TransactionController::class, 'export'])
            ->name('transactions.export');

        Route::get('transactions/export.csv', [\App\Http\Controllers\Api\TransactionController::class, 'export'])
            ->name('transactions.export.csv');

        Route::get('transactions/export.pdf', [\App\Http\Controllers\Api\TransactionController::class, 'export'])->name('transactions.export.pdf');

        Route::middleware('throttle:10,1')->group(function () {
            Route::get('/rates/convert', [ExchangeController::class, 'convert'])->name('rates.convert');
            Route::get('/rates/rate', [ExchangeController::class, 'rate'])->name('rates.rate');
            Route::get('/rates/currencies', [ExchangeController::class, 'currencies'])->name('rates.currencies');
        });

        Route::get('exchange/convert', [\App\Http\Controllers\Api\ExchangeController::class, 'convert'])
            ->name('exchange.convert');
        Route::get('alerts', [\App\Http\Controllers\Api\AlertController::class, 'index'])->name('alerts.index');
        Route::get('alerts/unread-count', [\App\Http\Controllers\Api\AlertController::class, 'unreadCount'])->name('alerts.unread_count');
        Route::patch('alerts/{alert}/read', [\App\Http\Controllers\Api\AlertController::class, 'markRead'])->name('alerts.mark_read');
        Route::patch('alerts/read-all', [\App\Http\Controllers\Api\AlertController::class, 'markAll'])
            ->name('alerts.mark_all');

            Route::get('reports/monthly', [\App\Http\Controllers\Api\ReportController::class, 'monthlyTotals'])
            ->name('reports.monthly');
        
        Route::get('reports/categories', [\App\Http\Controllers\Api\ReportController::class, 'categoryBreakdown'])
            ->name('reports.categories');

        // auth
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // REST resource rute (JSON)
        Route::apiResource('transactions', TransactionApiController::class);
        Route::apiResource('budgets', BudgetApiController::class);
        Route::apiResource('categories', CategoryApiController::class);
        Route::apiResource('savings-goals', SavingsGoalApiController::class)->names('savings_goals');

        //  3 različite dodatne API rute
        Route::get('transactions/summary', [ReportController::class, 'transactionsSummary'])
            ->name('transactions.summary');

        Route::get('categories/{category}/transactions', [TransactionApiController::class, 'byCategory'])
            ->name('categories.transactions');

        Route::post('transactions/bulk', [TransactionApiController::class, 'bulkStore'])
            ->middleware(['auth:sanctum', 'role:premium'])
            ->name('transactions.bulk');



        Route::get('user', function (Request $request) {
            return $request->user();
        })->name('user');

    });
});
