<?php

use Illuminate\Support\Facades\Route;
use Modules\Nesting\Http\Controllers\NestingController;

Route::middleware(['auth'])->group(function () {
    Route::post('/nesting/{nesting}/approve', [NestingController::class, 'approve'])->name('nesting.approve');
    Route::post('/nesting/{nesting}/confirm', [NestingController::class, 'confirm'])->name('nesting.confirm');
    Route::resource('nesting', NestingController::class);
});
