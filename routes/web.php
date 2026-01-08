<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'store']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/login/microsoft', [AuthController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/auth/callback', [AuthController::class, 'handleProviderCallback']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'showMainDashboard'])->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    // Other report routes
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/inventory/csv', [ReportController::class, 'inventoryCsv'])->name('reports.inventory.csv');
    Route::get('/reports/inventory/pdf', [ReportController::class, 'inventoryPdf'])->name('reports.inventory.pdf');
    Route::get('/reports/projects/{project}/bom', [ReportController::class, 'projectBom'])->name('reports.project.bom');
    Route::get('/reports/projects/{project}/bom/csv', [ReportController::class, 'projectBomCsv'])->name('reports.project.bom.csv');
    Route::get('/reports/projects/{project}/bom/pdf', [ReportController::class, 'projectBomPdf'])->name('reports.project.bom.pdf');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    Route::get('/admin/system', [AdminController::class, 'system'])->name('admin.system');
    Route::post('/admin/system/cache', [AdminController::class, 'clearCache'])->name('admin.system.cache');
});
