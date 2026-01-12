<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\ProductionController;
use Modules\Production\Http\Controllers\RoutingController;
use Modules\Production\Http\Controllers\TimeEntryController;

Route::middleware(['auth'])->group(function () {
    // Production Routes
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('/scan', [ProductionController::class, 'scan'])->name('production.scan');
    Route::post('/scan', [ProductionController::class, 'processScan'])->name('production.process-scan');

    // Routing Routes
    Route::get('/routing', [RoutingController::class, 'index'])->name('routing.index');
    Route::get('/part-instances/{part_instance}/routing/create', [RoutingController::class, 'create'])->name('routing.create');
    Route::post('/part-instances/{part_instance}/routing', [RoutingController::class, 'store'])->name('routing.store');
    Route::patch('/routing/{step}/status', [RoutingController::class, 'updateStatus'])->name('routing.update-status');

    // Time Entry Routes
    Route::get('/time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index');
    Route::post('/time-entries/start', [TimeEntryController::class, 'start'])->name('time-entries.start');
    Route::post('/routing/{step}/complete', [TimeEntryController::class, 'complete'])->name('time-entries.complete');
});
