<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DrawingController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\NestingController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoutingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\TimeEntryController;
use Illuminate\Support\Facades\Route;

// Root route - redirect to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/login/microsoft', [AuthController::class, 'redirectToProvider'])->name('login.microsoft');
Route::get('/login/microsoft/callback', [AuthController::class, 'handleProviderCallback']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ReportController::class, 'showMainDashboard'])->name('dashboard');

    // Customer Routes
    Route::get('/customers/import', [CustomerController::class, 'importForm'])->name('customers.import');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import.store');
    Route::resource('customers', CustomerController::class)->except(['show']);

    // Project Routes
    Route::resource('projects', ProjectController::class);

    // Purchase Order Routes
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('/projects/{project}/assemblies/create', [AssemblyController::class, 'create'])->name('projects.assemblies.create');
    Route::resource('assemblies', AssemblyController::class)->except(['index', 'create']);
    Route::get('/assemblies/{assembly}/parts/create', [PartController::class, 'create'])->name('assemblies.parts.create');
    Route::resource('parts', PartController::class)->except(['index', 'create']);

    // Drawing Routes
    Route::resource('drawings', DrawingController::class);
    Route::post('/drawings/{drawing}/upload', [DrawingController::class, 'upload'])->name('drawings.upload');

    // Shipping Routes
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::get('/shipping/create', [ShippingController::class, 'create'])->name('shipping.create');
    Route::post('/shipping', [ShippingController::class, 'store'])->name('shipping.store');
    Route::get('/shipping/{load}', [ShippingController::class, 'show'])->name('shipping.show');

    // Production Routes
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('/scan', [ProductionController::class, 'scan'])->name('production.scan');
    Route::post('/scan', [ProductionController::class, 'processScan'])->name('production.process-scan');

    // Routing Routes
    Route::get('/routing', [RoutingController::class, 'index'])->name('routing.index');
    Route::get('/part-instances/{part_instance}/routing/create', [RoutingController::class, 'create'])->name('routing.create');
    Route::post('/part-instances/{part_instance}/routing', [RoutingController::class, 'store'])->name('routing.store');
    Route::patch('/routing/{step}/status', [RoutingController::class, 'updateStatus'])->name('routing.update-status');

    // Time Entry Routes
    Route::get('/time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index');
    Route::post('/time-entries/start', [TimeEntryController::class, 'start'])->name('time-entries.start');
    Route::post('/routing/{step}/complete', [TimeEntryController::class, 'complete'])->name('time-entries.complete');

    // Label Routes
    Route::get('/labels/part/{part}', [LabelController::class, 'part'])->name('labels.part');
    Route::get('/labels/stock/{item}', [LabelController::class, 'stock'])->name('labels.stock');

    // Nesting Routes
    Route::resource('nesting', NestingController::class);

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Report Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/inventory/csv', [ReportController::class, 'inventoryCsv'])->name('reports.inventory.csv');
    Route::get('/reports/inventory/pdf', [ReportController::class, 'inventoryPdf'])->name('reports.inventory.pdf');
    Route::get('/reports/projects/{project}/bom', [ReportController::class, 'projectBom'])->name('reports.project.bom');
    Route::get('/reports/projects/{project}/bom/csv', [ReportController::class, 'projectBomCsv'])->name('reports.project.bom.csv');
    Route::get('/reports/projects/{project}/bom/pdf', [ReportController::class, 'projectBomPdf'])->name('reports.project.bom.pdf');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/system', [AdminController::class, 'system'])->name('system');
    Route::post('/system/clear-cache', [AdminController::class, 'clearCache'])->name('system.clear-cache');
});
