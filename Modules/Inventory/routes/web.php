<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\CycleCountController;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Inventory\Http\Controllers\ReceivingController;
use Modules\Inventory\Http\Controllers\StockActionController;

Route::middleware(['auth'])->group(function (): void {
    // Main inventory resource
    Route::resource('inventory', InventoryController::class);

    // PO Receiving
    Route::get('/purchase-orders/{purchase_order}/receive', [ReceivingController::class, 'create'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchase_order}/receive', [ReceivingController::class, 'store'])->name('purchase-orders.receive.store');

    // Stock Actions - Single Item
    Route::prefix('inventory/{inventory}')->name('inventory.')->group(function (): void {
        // Transfer
        Route::get('/transfer', [StockActionController::class, 'transferForm'])->name('transfer');
        Route::post('/transfer', [StockActionController::class, 'transfer'])->name('transfer.store');

        // Assign to Project
        Route::get('/assign', [StockActionController::class, 'assignForm'])->name('assign');
        Route::post('/assign', [StockActionController::class, 'assign'])->name('assign.store');

        // Release from Project
        Route::post('/release', [StockActionController::class, 'release'])->name('release');

        // Commit to Production
        Route::post('/commit', [StockActionController::class, 'commit'])->name('commit');

        // Mark as Used
        Route::post('/mark-used', [StockActionController::class, 'markUsed'])->name('mark-used');

        // Return to Free
        Route::post('/return', [StockActionController::class, 'return'])->name('return');

        // Adjust Quantity
        Route::get('/adjust', [StockActionController::class, 'adjustForm'])->name('adjust');
        Route::post('/adjust', [StockActionController::class, 'adjust'])->name('adjust.store');

        // Scrap
        Route::get('/scrap', [StockActionController::class, 'scrapForm'])->name('scrap');
        Route::post('/scrap', [StockActionController::class, 'scrap'])->name('scrap.store');

        // Create Remnant
        Route::get('/remnant', [StockActionController::class, 'remnantForm'])->name('remnant');
        Route::post('/remnant', [StockActionController::class, 'createRemnant'])->name('remnant.store');
    });

    // Bulk Stock Actions
    Route::post('/inventory-bulk-action', [StockActionController::class, 'bulkAction'])->name('inventory.bulk-action');

    // Cycle Count
    Route::prefix('cycle-counts')->name('cycle-counts.')->group(function (): void {
        Route::get('/', [CycleCountController::class, 'index'])->name('index');
        Route::get('/create', [CycleCountController::class, 'create'])->name('create');
        Route::post('/', [CycleCountController::class, 'store'])->name('store');
        Route::get('/{cycleCount}', [CycleCountController::class, 'show'])->name('show');
        Route::get('/{cycleCount}/count', [CycleCountController::class, 'countForm'])->name('count');
        Route::post('/{cycleCount}/count', [CycleCountController::class, 'recordCount'])->name('count.store');
        Route::post('/{cycleCount}/reconcile', [CycleCountController::class, 'reconcile'])->name('reconcile');
        Route::post('/{cycleCount}/complete', [CycleCountController::class, 'complete'])->name('complete');
    });
});
