<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolePermissionController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root redirect
Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Standard Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/refresh-captcha', [LoginController::class, 'refreshCaptcha'])->name('refresh-captcha');

    // Google OAuth Routes
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Logout (available for authenticated users)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Protected Routes - All Authenticated Users
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/realtime-data', [DashboardController::class, 'getRealtimeData'])->name('dashboard.realtime-data');
    Route::get('/dashboard/export', [DashboardController::class, 'exportDashboard'])->name('dashboard.export');

    /*
    |--------------------------------------------------------------------------
    | Budget Management Routes
    |--------------------------------------------------------------------------
    */
    // Read access for all authenticated users
    Route::get('/budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('/budget/export', [BudgetController::class, 'export'])->name('budget.export');
    Route::get('/budget/{id}', [BudgetController::class, 'show'])->name('budget.show')->where('id', '[0-9]+');

    // Budget Realizations Routes
    Route::get('/budget/realizations', [BudgetController::class, 'realizations'])->name('budget.realizations');
    Route::get('/budget/realization-detail/{id}', [BudgetController::class, 'realizationDetail'])->name('budget.realization-detail')->where('id', '[0-9]+');

    // Write access only for admin/pimpinan with budget management permission
    Route::middleware(['role:admin|pimpinan'])->group(function () {
        Route::get('/budget/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
        Route::get('/budget/{id}/edit', [BudgetController::class, 'edit'])->name('budget.edit')->where('id', '[0-9]+');
        Route::put('/budget/{id}', [BudgetController::class, 'update'])->name('budget.update')->where('id', '[0-9]+');
        Route::delete('/budget/{id}', [BudgetController::class, 'destroy'])->name('budget.destroy')->where('id', '[0-9]+');

        // Bulk Operations
        Route::delete('/budget/bulk-destroy', [BudgetController::class, 'bulkDestroy'])->name('budget.bulk-destroy');
        Route::get('/budget/{id}/deletion-preview', [BudgetController::class, 'deletionPreview'])->name('budget.deletion-preview')->where('id', '[0-9]+');

        // Import/Export Management
        Route::post('/budget/import', [BudgetController::class, 'import'])->name('budget.import');
        Route::get('/budget/template', [BudgetController::class, 'downloadTemplate'])->name('budget.template');
    });

    /*
    |--------------------------------------------------------------------------
    | Bills Management Routes
    |--------------------------------------------------------------------------
    */
    // Read access for all authenticated users
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');

    // AJAX Routes for Cascading Dropdowns (accessible by all authenticated users)
    Route::get('/bills/ajax/kros-by-kegiatan', [BillController::class, 'getKrosByKegiatan'])->name('bills.ajax.kros');
    Route::get('/bills/ajax/ros-by-kegiatan-kro', [BillController::class, 'getRosByKegiatanKro'])->name('bills.ajax.ros');
    Route::get('/bills/ajax/sub-komponens-by-all', [BillController::class, 'getSubKomponensByKegiatanKroRo'])->name('bills.ajax.sub-komponens');
    Route::get('/bills/ajax/maks-by-all', [BillController::class, 'getMaksByAll'])->name('bills.ajax.maks');

    // Create/Edit Bills - Available for PPK, Pimpinan, and Admin
    Route::middleware(['role:admin|pimpinan|ppk'])->group(function () {
        Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
        Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
        Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
        Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');

        // Duplicate bill for same date
        Route::post('/bills/duplicate-for-date', [BillController::class, 'duplicateForDate'])->name('bills.duplicate-for-date');

        // Delete bills (only if not SP2D status)
        Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    });

    // Bills Status Management and Approval - Admin and Pimpinan only
    Route::middleware(['role:admin|pimpinan'])->group(function () {
        Route::put('/bills/{bill}/status', [BillController::class, 'updateStatus'])->name('bills.update-status');
        Route::post('/bills/bulk-update-status', [BillController::class, 'bulkUpdateStatus'])->name('bills.bulk-update-status');
        Route::post('/bills/bulk-approve', [BillController::class, 'bulkApprove'])->name('bills.bulk-approve');
        Route::post('/bills/bulk-reject', [BillController::class, 'bulkReject'])->name('bills.bulk-reject');
    });

    // Bills Export and Reporting
    Route::get('/bills/export/excel', [BillController::class, 'exportExcel'])->name('bills.export.excel');
    Route::get('/bills/export/pdf', [BillController::class, 'exportPdf'])->name('bills.export.pdf');
    Route::post('/bills/import', [BillController::class, 'import'])->name('bills.import')->middleware(['role:admin|pimpinan']);

    /*
    |--------------------------------------------------------------------------
    | Reports Routes - Available for all authenticated users
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        // Budget Reports
        Route::get('/budget-realization', [ReportController::class, 'budgetRealization'])->name('budget-realization');
        Route::get('/budget-summary', [ReportController::class, 'budgetSummary'])->name('budget-summary');
        Route::get('/budget-performance', [ReportController::class, 'budgetPerformance'])->name('budget-performance');

        // Bills Reports
        Route::get('/bills', [ReportController::class, 'billsReport'])->name('bills');
        Route::get('/bills-status', [ReportController::class, 'billsStatusReport'])->name('bills-status');
        Route::get('/outstanding-bills', [ReportController::class, 'outstandingBills'])->name('outstanding-bills');

        // Analysis Reports
        Route::get('/monthly-comparison', [ReportController::class, 'monthlyComparison'])->name('monthly-comparison');
        Route::get('/quarterly-analysis', [ReportController::class, 'quarterlyAnalysis'])->name('quarterly-analysis');
        Route::get('/yearly-trend', [ReportController::class, 'yearlyTrend'])->name('yearly-trend');
        Route::get('/trend-analysis', [ReportController::class, 'trendAnalysis'])->name('trend-analysis');

        // PIC Performance Reports
        Route::get('/pic-performance', [ReportController::class, 'picPerformance'])->name('pic-performance');
        Route::get('/department-performance', [ReportController::class, 'departmentPerformance'])->name('department-performance');

        // Export routes for reports
        Route::get('/export/{type}', [ReportController::class, 'exportReport'])->name('export')->where('type', '[a-zA-Z-]+');
    });

    /*
    |--------------------------------------------------------------------------
    | User Management Routes - Admin only
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // User Status Management
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');

        // Bulk Operations
        Route::post('/bulk-activate', [UserController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])->name('bulk-deactivate');
        Route::delete('/bulk-delete', [UserController::class, 'bulkDelete'])->name('bulk-delete');

        // Import/Export
        Route::post('/import', [UserController::class, 'import'])->name('import');
        Route::get('/export', [UserController::class, 'export'])->name('export');
        Route::get('/template', [UserController::class, 'downloadTemplate'])->name('template');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes - Advanced Management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Role Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'rolesIndex'])->name('index');
        Route::get('/create', [RolePermissionController::class, 'rolesCreate'])->name('create');
        Route::post('/', [RolePermissionController::class, 'rolesStore'])->name('store');
        Route::get('/{role}', [RolePermissionController::class, 'rolesShow'])->name('show');
        Route::get('/{role}/edit', [RolePermissionController::class, 'rolesEdit'])->name('edit');
        Route::put('/{role}', [RolePermissionController::class, 'rolesUpdate'])->name('update');
        Route::delete('/{role}', [RolePermissionController::class, 'rolesDestroy'])->name('destroy');

        // Role Permissions Management
        Route::get('/{role}/permissions', [RolePermissionController::class, 'rolePermissions'])->name('permissions');
        Route::post('/{role}/permissions', [RolePermissionController::class, 'updateRolePermissions'])->name('permissions.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Permission Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'permissionsIndex'])->name('index');
        Route::get('/create', [RolePermissionController::class, 'permissionsCreate'])->name('create');
        Route::post('/', [RolePermissionController::class, 'permissionsStore'])->name('store');
        Route::get('/{permission}', [RolePermissionController::class, 'permissionsShow'])->name('show');
        Route::get('/{permission}/edit', [RolePermissionController::class, 'permissionsEdit'])->name('edit');
        Route::put('/{permission}', [RolePermissionController::class, 'permissionsUpdate'])->name('update');
        Route::delete('/{permission}', [RolePermissionController::class, 'permissionsDestroy'])->name('destroy');

        // Permission Categories
        Route::get('/categories', [RolePermissionController::class, 'permissionCategories'])->name('categories');
        Route::post('/categories', [RolePermissionController::class, 'createPermissionCategory'])->name('categories.store');
    });

    /*
    |--------------------------------------------------------------------------
    | User Role Assignment Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('user-roles')->name('user-roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'userRoles'])->name('index');
        Route::get('/assign', [RolePermissionController::class, 'assignRoleForm'])->name('assign');
        Route::post('/assign', [RolePermissionController::class, 'assignUserRole'])->name('store');
        Route::delete('/users/{user}/roles/{role}', [RolePermissionController::class, 'removeUserRole'])->name('remove');

        // Individual user role management
        Route::get('/users/{user}', [RolePermissionController::class, 'userRoleDetail'])->name('user.detail');
        Route::post('/users/{user}/roles', [RolePermissionController::class, 'assignRoleToUser'])->name('user.assign');
    });

    /*
    |--------------------------------------------------------------------------
    | Bulk Operations Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('bulk')->name('bulk.')->group(function () {
        // Bulk role assignment
        Route::post('/assign-role', [RolePermissionController::class, 'bulkAssignRole'])->name('assign-role');
        Route::post('/remove-role', [RolePermissionController::class, 'bulkRemoveRole'])->name('remove-role');

        // Permission synchronization
        Route::post('/sync-permissions', [RolePermissionController::class, 'syncDefaultPermissions'])->name('sync-permissions');
        Route::post('/reset-permissions', [RolePermissionController::class, 'resetPermissions'])->name('reset-permissions');

        // User operations
        Route::post('/activate-users', [RolePermissionController::class, 'bulkActivateUsers'])->name('activate-users');
        Route::post('/deactivate-users', [RolePermissionController::class, 'bulkDeactivateUsers'])->name('deactivate-users');
    });

    /*
    |--------------------------------------------------------------------------
    | System Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('system')->name('system.')->group(function () {
        // System settings
        Route::get('/settings', [RolePermissionController::class, 'systemSettings'])->name('settings');
        Route::post('/settings', [RolePermissionController::class, 'updateSystemSettings'])->name('settings.update');

        // Database management
        Route::get('/database', [RolePermissionController::class, 'databaseManagement'])->name('database');
        Route::post('/database/backup', [RolePermissionController::class, 'backupDatabase'])->name('database.backup');
        Route::post('/database/optimize', [RolePermissionController::class, 'optimizeDatabase'])->name('database.optimize');

        // Cache management
        Route::post('/cache/clear', [RolePermissionController::class, 'clearCache'])->name('cache.clear');
        Route::post('/cache/optimize', [RolePermissionController::class, 'optimizeCache'])->name('cache.optimize');

        // Logs management
        Route::get('/logs', [RolePermissionController::class, 'viewLogs'])->name('logs');
        Route::post('/logs/clear', [RolePermissionController::class, 'clearLogs'])->name('logs.clear');
        Route::get('/logs/download', [RolePermissionController::class, 'downloadLogs'])->name('logs.download');
    });

    /*
    |--------------------------------------------------------------------------
    | Audit & Monitoring Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/activity', [RolePermissionController::class, 'activityLog'])->name('activity');
        Route::get('/login-history', [RolePermissionController::class, 'loginHistory'])->name('login-history');
        Route::get('/user-actions', [RolePermissionController::class, 'userActions'])->name('user-actions');
        Route::get('/system-events', [RolePermissionController::class, 'systemEvents'])->name('system-events');

        // Export audit logs
        Route::get('/export/{type}', [RolePermissionController::class, 'exportAuditLog'])->name('export')->where('type', '[a-zA-Z-]+');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes for AJAX Calls
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api/v1')->name('api.')->group(function () {
    // Budget API
    Route::prefix('budget')->name('budget.')->group(function () {
        Route::get('/search', [BudgetController::class, 'apiSearch'])->name('search');
        Route::get('/categories', [BudgetController::class, 'apiCategories'])->name('categories');
        Route::get('/{budget}/realization', [BudgetController::class, 'apiRealization'])->name('realization');
    });

    // Bills API
    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/search', [BillController::class, 'apiSearch'])->name('search');
        Route::get('/status-summary', [BillController::class, 'apiStatusSummary'])->name('status-summary');
        Route::get('/monthly-data', [BillController::class, 'apiMonthlyData'])->name('monthly-data');
    });

    // Dashboard API
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'apiStats'])->name('stats');
        Route::get('/charts-data', [DashboardController::class, 'apiChartsData'])->name('charts-data');
        Route::get('/alerts', [DashboardController::class, 'apiAlerts'])->name('alerts');
    });

    // User API (Admin only)
    Route::middleware(['role:admin'])->prefix('users')->name('users.')->group(function () {
        Route::get('/search', [UserController::class, 'apiSearch'])->name('search');
        Route::get('/roles', [UserController::class, 'apiRoles'])->name('roles');
    });
});

/*
|--------------------------------------------------------------------------
| File Download Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('download')->name('download.')->group(function () {
    // Budget downloads
    Route::get('/budget-template', [BudgetController::class, 'downloadTemplate'])->name('budget.template');
    Route::get('/budget-export/{format}', [BudgetController::class, 'downloadExport'])->name('budget.export')->where('format', '(xlsx|csv|pdf)');

    // Bills downloads
    Route::get('/bills-export/{format}', [BillController::class, 'downloadExport'])->name('bills.export')->where('format', '(xlsx|csv|pdf)');

    // Reports downloads
    Route::get('/report/{type}/{format}', [ReportController::class, 'downloadReport'])->name('report')->where(['type' => '[a-zA-Z-]+', 'format' => '(xlsx|csv|pdf)']);

    // System downloads (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/user-template', [UserController::class, 'downloadTemplate'])->name('user.template');
        Route::get('/backup/{file}', [RolePermissionController::class, 'downloadBackup'])->name('backup')->where('file', '[a-zA-Z0-9._-]+');
    });
});

/*
|--------------------------------------------------------------------------
| Health Check Routes (for monitoring)
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment(),
    ]);
})->name('health.check');

Route::middleware(['auth', 'role:admin'])->get('/health/detailed', function () {
    try {
        $dbStatus = DB::connection()->getPdo() ? 'connected' : 'disconnected';
    } catch (Exception $e) {
        $dbStatus = 'disconnected';
    }

    try {
        Cache::put('health_check', 'test', 60);
        $cacheStatus = Cache::get('health_check') === 'test' ? 'working' : 'not working';
        Cache::forget('health_check');
    } catch (Exception $e) {
        $cacheStatus = 'not working';
    }

    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'environment' => app()->environment(),
        'database' => $dbStatus,
        'cache' => $cacheStatus,
        'storage' => is_writable(storage_path()) ? 'writable' : 'not writable',
    ]);
})->name('health.detailed');

/*
|--------------------------------------------------------------------------
| Error Handling Routes
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
