<?php

// routes/web.php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DrawingController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store'); // Added name!
Route::get('/login/microsoft', [AuthController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [AuthController::class, 'handleProviderCallback']);

// Authenticated Routes Group
Route::middleware(['auth'])->group(function (): void {
    // Moved Logout here (makes more sense logically)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [ReportController::class, 'showMainDashboard'])->name('dashboard');

    // Customer CRUD Routes
    Route::get('/customers/import', [CustomerController::class, 'importForm'])->name('customers.import');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import.store');
    Route::resource('customers', CustomerController::class)->except(['show']);

    // Project CRUD Routes
    Route::resource('projects', ProjectController::class);

    // Drawing Routes - FIXED: Removed manual 'show' route and removed 'except'
    Route::resource('drawings', DrawingController::class);
    Route::post('/drawings/{drawing}/upload', [DrawingController::class, 'upload'])->name('drawings.upload');

    // Shipping Routes
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');

    // Reporting Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/inventory/csv', [ReportController::class, 'inventoryCsv'])->name('reports.inventory.csv');
    Route::get('/reports/inventory/pdf', [ReportController::class, 'inventoryPdf'])->name('reports.inventory.pdf');
    Route::get('/reports/project/{project}/bom', [ReportController::class, 'projectBom'])->name('reports.bom');
    Route::get('/reports/project/{project}/bom/csv', [ReportController::class, 'projectBomCsv'])->name('reports.bom.csv');
    Route::get('/reports/project/{project}/bom/pdf', [ReportController::class, 'projectBomPdf'])->name('reports.bom.pdf');

    // Settings Routes - FIXED: Used imported class
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Production Routes - FIXED: Used imported class
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('/scan', [ProductionController::class, 'scan'])->name('production.scan');
    Route::post('/scan', [ProductionController::class, 'processScan'])->name('production.process-scan');

    // Label Routes - FIXED: Used imported class
    Route::get('/labels/part/{part}', [LabelController::class, 'part'])->name('labels.part');
    Route::get('/labels/stock/{item}', [LabelController::class, 'stock'])->name('labels.stock');

    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function (): void {
        // FIXED: Used imported class
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/system', [AdminController::class, 'system'])->name('system');
        Route::post('/system/clear-cache', [AdminController::class, 'clearCache'])->name('system.clear-cache');
    });
});
