<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\ShippingController;

Route::middleware(['auth'])->group(function () {
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::get('/shipping/create', [ShippingController::class, 'create'])->name('shipping.create');
    Route::post('/shipping', [ShippingController::class, 'store'])->name('shipping.store');
    Route::get('/shipping/{load}', [ShippingController::class, 'show'])->name('shipping.show');
    Route::post('/shipping/{load}/add-item', [ShippingController::class, 'addItem'])->name('shipping.add-item');
    Route::post('/shipping/{load}/ship', [ShippingController::class, 'ship'])->name('shipping.ship');
    Route::get('/shipping/{load}/bol', [ShippingController::class, 'printBol'])->name('shipping.print-bol');
});
