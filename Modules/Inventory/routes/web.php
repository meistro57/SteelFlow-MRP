<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['auth'])->group(function (): void {
    Route::resource('inventory', InventoryController::class);
});
