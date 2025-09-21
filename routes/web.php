<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
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
    Route::get('/budget/export', [BudgetController::class, 'export'])->name('budget.export');
    Route::get('/budget/{budget}', [BudgetController::class, 'show'])->name('budget.show');

    // Budget Management - Write access only for admin/pimpinan
    Route::middleware('permission:manage budget')->group(function () {
        Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
        Route::get('/budget/{budget}/edit', [BudgetController::class, 'edit'])->name('budget.edit');
        Route::put('/budget/{budget}', [BudgetController::class, 'update'])->name('budget.update');
        Route::delete('/budget/{budget}', [BudgetController::class, 'destroy'])->name('budget.destroy');
    });

    // Bills Management
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');

    // Bills - Create/Edit for PPK and above
    Route::middleware('permission:create bills')->group(function () {
        Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
        Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
        Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    });

    // Bills - Approval for admin/pimpinan
    Route::middleware('permission:approve bills')->group(function () {
        Route::put('/bills/{bill}/status', [BillController::class, 'updateStatus'])->name('bills.update-status');
        Route::post('/bills/bulk-update-status', [BillController::class, 'bulkUpdateStatus'])->name('bills.bulk-update-status');
    });

    // User Management - Admin only
    Route::middleware('permission:manage users')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // Reports
    Route::middleware('permission:view reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/budget-realization', [ReportController::class, 'budgetRealization'])->name('reports.budget-realization');
        Route::get('/reports/bills', [ReportController::class, 'billsReport'])->name('reports.bills');
        Route::get('/reports/monthly-comparison', [ReportController::class, 'monthlyComparison'])->name('reports.monthly-comparison');
    });
});
