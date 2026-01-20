<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DrawingController;
use App\Http\Controllers\GasBottleController;
use App\Http\Controllers\ImportTemplateController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\UIEditorController;
use App\Http\Controllers\UpfImportController;
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

    // Import Template Routes
    Route::get('/import-templates', [ImportTemplateController::class, 'index'])->name('import-templates.index');
    Route::get('/import-templates/{type}/download', [ImportTemplateController::class, 'download'])->name('import-templates.download');
    Route::get('/import-templates/customers', [ImportTemplateController::class, 'customers'])->name('import-templates.customers');
    Route::get('/import-templates/kiss', [ImportTemplateController::class, 'kiss'])->name('import-templates.kiss');
    Route::get('/import-templates/xsr', [ImportTemplateController::class, 'xsr'])->name('import-templates.xsr');
    Route::get('/import-templates/upf', [ImportTemplateController::class, 'upf'])->name('import-templates.upf');

    // UPF Import Routes
    Route::get('/upf/import', [UpfImportController::class, 'index'])->name('upf-import.index');
    Route::post('/upf/import', [UpfImportController::class, 'store'])->name('upf-import.store');

    // Customer Routes
    Route::get('/customers/import', [CustomerController::class, 'importForm'])->name('customers.import');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import.store');
    Route::resource('customers', CustomerController::class)->except(['show']);

    // Project Routes
    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{project}/import-kiss', [ProjectController::class, 'importKissForm'])->name('projects.import-kiss');
    Route::post('/projects/{project}/import-kiss', [ProjectController::class, 'importKiss'])->name('projects.import-kiss.store');
    Route::get('/projects/{project}/import-xsr', [ProjectController::class, 'importXsrForm'])->name('projects.import-xsr');
    Route::post('/projects/{project}/import-xsr', [ProjectController::class, 'importXsr'])->name('projects.import-xsr.store');

    // Purchase Order Routes
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('/projects/{project}/assemblies/create', [AssemblyController::class, 'create'])->name('projects.assemblies.create');
    Route::resource('assemblies', AssemblyController::class)->except(['index', 'create']);
    Route::get('/assemblies/{assembly}/parts/create', [PartController::class, 'create'])->name('assemblies.parts.create');
    Route::resource('parts', PartController::class)->except(['index', 'create']);

    // Drawing Routes
    Route::resource('drawings', DrawingController::class);
    Route::post('/drawings/{drawing}/upload', [DrawingController::class, 'upload'])->name('drawings.upload');

    // Label Routes
    Route::get('/labels/part/{part}', [LabelController::class, 'part'])->name('labels.part');
    Route::get('/labels/stock/{item}', [LabelController::class, 'stock'])->name('labels.stock');

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
    Route::get('/reports/production', [ReportController::class, 'production'])->name('reports.production');
    Route::get('/reports/labor-efficiency', [ReportController::class, 'laborEfficiency'])->name('reports.labor-efficiency');
    Route::get('/reports/batch-completion', [ReportController::class, 'batchCompletion'])->name('reports.batch-completion');

    // UI Editor / Dashboard Builder Routes
    Route::prefix('ui-editor')->name('ui-editor.')->group(function () {
        Route::get('/', [UIEditorController::class, 'index'])->name('index');
        Route::get('/create', [UIEditorController::class, 'create'])->name('create');
        Route::post('/', [UIEditorController::class, 'store'])->name('store');
        Route::get('/{dashboard}', [UIEditorController::class, 'show'])->name('show');
        Route::get('/{dashboard}/edit', [UIEditorController::class, 'edit'])->name('edit');
        Route::put('/{dashboard}', [UIEditorController::class, 'update'])->name('update');
        Route::delete('/{dashboard}', [UIEditorController::class, 'destroy'])->name('destroy');
        Route::post('/{dashboard}/set-default', [UIEditorController::class, 'setDefault'])->name('set-default');
        Route::post('/{dashboard}/duplicate', [UIEditorController::class, 'duplicate'])->name('duplicate');

        // Widget management (JSON responses for AJAX)
        Route::post('/{dashboard}/widgets', [UIEditorController::class, 'addWidget'])->name('widgets.store');
        Route::put('/{dashboard}/widgets/{widget}', [UIEditorController::class, 'updateWidget'])->name('widgets.update');
        Route::delete('/{dashboard}/widgets/{widget}', [UIEditorController::class, 'removeWidget'])->name('widgets.destroy');
        Route::put('/{dashboard}/layout', [UIEditorController::class, 'updateLayout'])->name('layout.update');
        Route::get('/{dashboard}/widgets/{widget}/data', [UIEditorController::class, 'getWidgetData'])->name('widgets.data');
    });

    // Gas Bottle Rental Routes
    Route::prefix('gas-bottles')->name('gas-bottles.')->group(function () {
        Route::get('/', [GasBottleController::class, 'index'])->name('index');
        Route::get('/create', [GasBottleController::class, 'create'])->name('create');
        Route::post('/', [GasBottleController::class, 'store'])->name('store');
        Route::get('/{gasBottle}', [GasBottleController::class, 'show'])->name('show');
        Route::post('/{gasBottle}/return', [GasBottleController::class, 'return'])->name('return');
        Route::post('/{gasBottle}/swap', [GasBottleController::class, 'swap'])->name('swap');
        Route::post('/{gasBottle}/flag', [GasBottleController::class, 'flag'])->name('flag');
        Route::post('/{gasBottle}/schedule-inspection', [GasBottleController::class, 'scheduleInspection'])->name('schedule-inspection');
    });

    // Shipping & Load Routes
    Route::prefix('shipping')->name('shipping.')->group(function () {
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::get('/create', [ShippingController::class, 'create'])->name('create');
        Route::post('/', [ShippingController::class, 'store'])->name('store');
        Route::get('/{load}', [ShippingController::class, 'show'])->name('show');
        Route::get('/{load}/edit', [ShippingController::class, 'edit'])->name('edit');
        Route::put('/{load}', [ShippingController::class, 'update'])->name('update');
        Route::delete('/{load}', [ShippingController::class, 'destroy'])->name('destroy');
        Route::post('/{load}/add-item', [ShippingController::class, 'addItem'])->name('add-item');
        Route::delete('/{load}/items/{item}', [ShippingController::class, 'removeItem'])->name('remove-item');
        Route::post('/{load}/plan', [ShippingController::class, 'plan'])->name('plan');
        Route::post('/{load}/ship', [ShippingController::class, 'ship'])->name('ship');
        Route::post('/{load}/deliver', [ShippingController::class, 'deliver'])->name('deliver');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/system', [AdminController::class, 'system'])->name('system');
    Route::post('/system/clear-cache', [AdminController::class, 'clearCache'])->name('system.clear-cache');
});
