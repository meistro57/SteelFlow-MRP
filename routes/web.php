<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication Routes
Route::get('/login/microsoft', [AuthController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [AuthController::class, 'handleProviderCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');

    Route::get('/projects', function () {
        return Inertia::render('Projects/Index');
    })->name('projects');

    Route::get('/inventory', function () {
        return Inertia::render('Inventory/Index');
    })->name('inventory');

    // Reporting Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/project/{project}/bom', [ReportController::class, 'projectBom'])->name('reports.bom');
});
