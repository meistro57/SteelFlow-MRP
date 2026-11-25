<?php

use Illuminate\Support\Facades\Route;
use Modules\CncData\Http\Controllers\Api\CncProgramController;

Route::middleware(config('cncdata.api_middleware'))
    ->prefix('api/cnc')
    ->name('api.cnc.')
    ->group(function () {
        Route::post('/analyze', [CncProgramController::class, 'analyze'])->name('analyze');
        Route::get('/rates', [CncProgramController::class, 'rateBook'])->name('rates');

        Route::get('/programs', [CncProgramController::class, 'index'])->name('programs.index');
        Route::post('/programs', [CncProgramController::class, 'store'])->name('programs.store');
        Route::get('/programs/{cncProgram}', [CncProgramController::class, 'show'])->name('programs.show');
        Route::post('/programs/{cncProgram}/reanalyze', [CncProgramController::class, 'reanalyze'])->name('programs.reanalyze');
        Route::post('/programs/{cncProgram}/approve', [CncProgramController::class, 'approve'])->name('programs.approve');
        Route::put('/programs/{cncProgram}/parts', [CncProgramController::class, 'syncParts'])->name('programs.parts');
        Route::post('/programs/{cncProgram}/runs', [CncProgramController::class, 'recordRun'])->name('programs.runs');
        Route::get('/programs/{cncProgram}/cost', [CncProgramController::class, 'cost'])->name('programs.cost');
    });
