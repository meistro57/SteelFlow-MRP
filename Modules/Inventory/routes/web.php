<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['auth'])->group(function () {
    Route::resource('inventory', InventoryController::class);
});
