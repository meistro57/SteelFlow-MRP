<?php

use Illuminate\Support\Facades\Route;
use Modules\NCR\Http\Controllers\NCRController;

Route::middleware(['auth'])->prefix('quality')->name('ncr.')->group(function () {
    // NCR Routes
    Route::get('/ncr', [NCRController::class, 'index'])->name('index');
    Route::get('/ncr/create', [NCRController::class, 'create'])->name('create');
    Route::post('/ncr', [NCRController::class, 'store'])->name('store');
    Route::get('/ncr/{ncr}', [NCRController::class, 'show'])->name('show');
    Route::post('/ncr/{ncr}/disposition', [NCRController::class, 'disposition'])->name('disposition');
    Route::post('/ncr/{ncr}/close', [NCRController::class, 'close'])->name('close');
    Route::delete('/ncr/{ncr}', [NCRController::class, 'destroy'])->name('destroy');
});
