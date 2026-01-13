<?php

use Illuminate\Support\Facades\Route;
use Modules\ShopTicket\Http\Controllers\ShopTicketController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('shop-tickets', ShopTicketController::class);
    Route::post('shop-tickets/{shopTicket}/release', [ShopTicketController::class, 'release'])
        ->name('shop-tickets.release');
    Route::post('shop-tickets/{shopTicket}/steps/{step}/complete', [ShopTicketController::class, 'completeStep'])
        ->name('shop-tickets.steps.complete');
});
