<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Inventory\Http\Controllers\ReceivingController;

Route::middleware(['auth'])->group(function (): void {
    Route::resource('inventory', InventoryController::class);

    Route::get('/purchase-orders/{purchase_order}/receive', [ReceivingController::class, 'create'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchase_order}/receive', [ReceivingController::class, 'store'])->name('purchase-orders.receive.store');
});
