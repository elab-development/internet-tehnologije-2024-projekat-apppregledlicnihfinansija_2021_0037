<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// WEB kontroleri (HTML/Blade)
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SavingsGoalController;

// Početna (public)
Route::get('/', fn () => view('welcome'))->name('home');

// Za ulogovane i (po potrebi) verifikovane
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resource rute za stranice (HTML)
    Route::resource('transactions', TransactionController::class);
    Route::resource('budgets', BudgetController::class);
    Route::resource('categories', CategoryController::class);

    // Lep URL sa crticom, ali imena ruta sa donjom crtom (da bi radilo: route('savings_goals.index'))
    Route::resource('savings-goals', SavingsGoalController::class)->names('savings_goals');
});

// Profil (ulogovani)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Breeze / auth rute (login, register, logout)
require __DIR__.'/auth.php';
