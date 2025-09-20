<?php
// routes/web.php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/refresh-captcha', [LoginController::class, 'refreshCaptcha']);

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Budget Management - Read access for all authenticated users
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/{budget}', [BudgetController::class, 'show'])->name('budget.show');

    // Budget Management - Write access only for admin/pimpinan
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
        Route::get('/budget/{budget}/edit', [BudgetController::class, 'edit'])->name('budget.edit');
        Route::put('/budget/{budget}', [BudgetController::class, 'update'])->name('budget.update');
        Route::delete('/budget/{budget}', [BudgetController::class, 'destroy'])->name('budget.destroy');
    });
});
