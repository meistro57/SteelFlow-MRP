<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/login/microsoft', [AuthController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [AuthController::class, 'handleProviderCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');

    // Project CRUD Routes
    Route::resource('projects', ProjectController::class);

    // Inventory CRUD Routes
    Route::resource('inventory', InventoryController::class);

    // Reporting Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/project/{project}/bom', [ReportController::class, 'projectBom'])->name('reports.bom');

    // Settings Routes
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

    // Production Routes
    Route::get('/scan', [\App\Http\Controllers\ProductionController::class, 'scan'])->name('production.scan');
    Route::post('/scan', [\App\Http\Controllers\ProductionController::class, 'processScan'])->name('production.process-scan');

    // Label Routes
    Route::get('/labels/part/{part}', [\App\Http\Controllers\LabelController::class, 'part'])->name('labels.part');
    Route::get('/labels/stock/{item}', [\App\Http\Controllers\LabelController::class, 'stock'])->name('labels.stock');

    // Drawing Routes
    Route::get('/drawings/{drawing}', [\App\Http\Controllers\DrawingController::class, 'show'])->name('drawings.show');
    Route::post('/drawings/{drawing}/upload', [\App\Http\Controllers\DrawingController::class, 'upload'])->name('drawings.upload');
});
