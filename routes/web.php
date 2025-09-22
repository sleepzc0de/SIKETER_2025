<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolePermissionController;
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
    Route::get('/budget/{id}', [BudgetController::class, 'show'])->name('budget.show')->where('id', '[0-9]+');

    // Budget Management - Write access only for admin/pimpinan
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
        Route::get('/budget/{id}/edit', [BudgetController::class, 'edit'])->name('budget.edit')->where('id', '[0-9]+');
        Route::put('/budget/{id}', [BudgetController::class, 'update'])->name('budget.update')->where('id', '[0-9]+');
        Route::delete('/budget/{id}', [BudgetController::class, 'destroy'])->name('budget.destroy')->where('id', '[0-9]+');
        Route::delete('/budget', [BudgetController::class, 'bulkDestroy'])->name('budget.bulk-destroy');
        Route::get('/budget/{id}/deletion-preview', [BudgetController::class, 'deletionPreview'])->where('id', '[0-9]+');
    });

    // Bills Management - All authenticated users can view
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{id}', [BillController::class, 'show'])->name('bills.show')->where('id', '[0-9]+');

    // Bills - Create/Edit for PPK and above
    Route::middleware('role:admin,pimpinan,ppk')->group(function () {
        Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('/bills/{id}/edit', [BillController::class, 'edit'])->name('bills.edit')->where('id', '[0-9]+');
        Route::put('/bills/{id}', [BillController::class, 'update'])->name('bills.update')->where('id', '[0-9]+');
        Route::delete('/bills/{id}', [BillController::class, 'destroy'])->name('bills.destroy')->where('id', '[0-9]+');
    });

    // Bills - Approval for admin/pimpinan
    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::put('/bills/{id}/status', [BillController::class, 'updateStatus'])->name('bills.update-status')->where('id', '[0-9]+');
        Route::post('/bills/bulk-update-status', [BillController::class, 'bulkUpdateStatus'])->name('bills.bulk-update-status');
    });

    // User Management - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show')->where('id', '[0-9]+');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->where('id', '[0-9]+');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update')->where('id', '[0-9]+');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy')->where('id', '[0-9]+');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->where('id', '[0-9]+');
    });

    // Reports - Available for all authenticated users
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/budget-realization', [ReportController::class, 'budgetRealization'])->name('reports.budget-realization');
    Route::get('/reports/bills', [ReportController::class, 'billsReport'])->name('reports.bills');
    Route::get('/reports/monthly-comparison', [ReportController::class, 'monthlyComparison'])->name('reports.monthly-comparison');
    Route::get('/reports/trend-analysis', [ReportController::class, 'trendAnalysis'])->name('reports.trend-analysis');


    // Budget Realizations
    Route::get('/budget-realizations', [BudgetController::class, 'realizations'])->name('budget.realizations');
    Route::get('/budget-realizations/{id}', [BudgetController::class, 'realizationDetail'])->name('budget.realization-detail')->where('id', '[0-9]+');
});

// Admin Panel Routes - Role & Permission Management
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Roles Management
    Route::get('/roles', [RolePermissionController::class, 'rolesIndex'])->name('roles.index');
    Route::get('/roles/create', [RolePermissionController::class, 'rolesCreate'])->name('roles.create');
    Route::post('/roles', [RolePermissionController::class, 'rolesStore'])->name('roles.store');
    Route::get('/roles/{id}', [RolePermissionController::class, 'rolesShow'])->name('roles.show')->where('id', '[0-9]+');
    Route::get('/roles/{id}/edit', [RolePermissionController::class, 'rolesEdit'])->name('roles.edit')->where('id', '[0-9]+');
    Route::put('/roles/{id}', [RolePermissionController::class, 'rolesUpdate'])->name('roles.update')->where('id', '[0-9]+');
    Route::delete('/roles/{id}', [RolePermissionController::class, 'rolesDestroy'])->name('roles.destroy')->where('id', '[0-9]+');

    // Permissions Management
    Route::get('/permissions', [RolePermissionController::class, 'permissionsIndex'])->name('permissions.index');
    Route::get('/permissions/create', [RolePermissionController::class, 'permissionsCreate'])->name('permissions.create');
    Route::post('/permissions', [RolePermissionController::class, 'permissionsStore'])->name('permissions.store');
    Route::get('/permissions/{id}', [RolePermissionController::class, 'permissionsShow'])->name('permissions.show')->where('id', '[0-9]+');
    Route::get('/permissions/{id}/edit', [RolePermissionController::class, 'permissionsEdit'])->name('permissions.edit')->where('id', '[0-9]+');
    Route::put('/permissions/{id}', [RolePermissionController::class, 'permissionsUpdate'])->name('permissions.update')->where('id', '[0-9]+');
    Route::delete('/permissions/{id}', [RolePermissionController::class, 'permissionsDestroy'])->name('permissions.destroy')->where('id', '[0-9]+');

    // User Role Assignment
    Route::get('/user-roles', [RolePermissionController::class, 'userRoles'])->name('user-roles.index');
    Route::post('/users/{user}/assign-role', [RolePermissionController::class, 'assignUserRole'])->name('users.assign-role')->where('user', '[0-9]+');
    Route::delete('/users/{user}/roles/{role}', [RolePermissionController::class, 'removeUserRole'])->name('users.remove-role')->where(['user' => '[0-9]+', 'role' => '[0-9]+']);

    // Bulk Operations
    Route::post('/bulk-assign-role', [RolePermissionController::class, 'bulkAssignRole'])->name('bulk-assign-role');
    Route::post('/sync-default-permissions', [RolePermissionController::class, 'syncDefaultPermissions'])->name('sync-permissions');
});
