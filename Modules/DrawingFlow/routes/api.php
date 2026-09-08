<?php

use Illuminate\Support\Facades\Route;
use Modules\DrawingFlow\Http\Controllers\DrawingFlowController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('drawingflows', DrawingFlowController::class)->names('drawingflow');
});
