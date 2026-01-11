<?php

use Illuminate\Support\Facades\Route;

Route::prefix('shopticket')->group(function() {
    Route::get('/', 'ShopTicketController@index');
});
