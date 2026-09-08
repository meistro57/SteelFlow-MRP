<?php

use Illuminate\Support\Facades\Route;
use Modules\DrawingFlow\Http\Controllers\DrawingRequestController;
use Modules\DrawingFlow\Http\Controllers\FabQueueController;
use Modules\DrawingFlow\Http\Controllers\ProjectAttachmentController;
use Modules\DrawingFlow\Http\Controllers\SubmittalController;
use Modules\DrawingFlow\Http\Controllers\SubmittalFileController;
use Modules\DrawingFlow\Http\Controllers\SubmittalNoteController;
use Modules\DrawingFlow\Http\Controllers\SubmittalPdfMarkupController;

Route::middleware(['auth'])->group(function (): void {
    // Project attachments
    Route::get('projects/{project}/attachments/{attachment}/view', [ProjectAttachmentController::class, 'view'])->name('projects.attachments.view');
    Route::get('projects/{project}/attachments/{attachment}/download', [ProjectAttachmentController::class, 'download'])->name('projects.attachments.download');
    Route::delete('projects/{project}/attachments/{attachment}', [ProjectAttachmentController::class, 'destroy'])->name('projects.attachments.destroy');

    // Drawing Requests
    Route::resource('drawing-requests', DrawingRequestController::class);
    Route::post('drawing-requests/{drawing_request}/assign', [DrawingRequestController::class, 'assign'])->name('drawing-requests.assign');
    Route::post('drawing-requests/{drawing_request}/mark-ready', [DrawingRequestController::class, 'markReady'])->name('drawing-requests.mark-ready');
    Route::post('drawing-requests/{drawing_request}/cancel', [DrawingRequestController::class, 'cancel'])->name('drawing-requests.cancel');
    Route::post('drawing-requests/{drawing_request}/hold', [DrawingRequestController::class, 'hold'])->name('drawing-requests.hold');

    // Submittals
    Route::get('submittals', [SubmittalController::class, 'index'])->name('submittals.index');
    Route::get('submittals/{submittal}', [SubmittalController::class, 'show'])->name('submittals.show');
    Route::delete('submittals/{submittal}', [SubmittalController::class, 'destroy'])->name('submittals.destroy');
    Route::post('submittals/from-request/{drawing_request}', [SubmittalController::class, 'createFromRequest'])->name('submittals.create-from-request');
    Route::patch('submittals/{submittal}/purpose', [SubmittalController::class, 'updatePurpose'])->name('submittals.purpose.update');
    Route::post('submittals/{submittal}/submit', [SubmittalController::class, 'submit'])->name('submittals.submit');
    Route::post('submittals/{submittal}/process-approval', [SubmittalController::class, 'processApproval'])->name('submittals.process-approval');
    Route::post('submittals/{submittal}/create-revision', [SubmittalController::class, 'createRevision'])->name('submittals.create-revision');
    Route::post('submittals/{submittal}/files', [SubmittalFileController::class, 'store'])->name('submittals.files.store');
    Route::get('submittals/{submittal}/files/{submittalFile}/view', [SubmittalFileController::class, 'view'])->name('submittals.files.view');
    Route::get('submittals/{submittal}/files/{submittalFile}/download', [SubmittalFileController::class, 'download'])->name('submittals.files.download');
    Route::get('submittals/{submittal}/files/{submittalFile}/markups', [SubmittalPdfMarkupController::class, 'index'])->name('submittals.files.markups.index');
    Route::post('submittals/{submittal}/files/{submittalFile}/markups', [SubmittalPdfMarkupController::class, 'store'])->name('submittals.files.markups.store');
    Route::post('submittals/{submittal}/files/{submittalFile}/markups/import', [SubmittalPdfMarkupController::class, 'import'])->name('submittals.files.markups.import');
    Route::put('submittals/{submittal}/files/{submittalFile}/markups/{markup}', [SubmittalPdfMarkupController::class, 'update'])->name('submittals.files.markups.update');
    Route::delete('submittals/{submittal}/files/{submittalFile}/markups/{markup}', [SubmittalPdfMarkupController::class, 'destroy'])->name('submittals.files.markups.destroy');
    Route::get('submittals/{submittal}/files/{submittalFile}/page-scales', [SubmittalPdfMarkupController::class, 'scaleIndex'])->name('submittals.files.page-scales.index');
    Route::put('submittals/{submittal}/files/{submittalFile}/page-scales/{pageNumber}', [SubmittalPdfMarkupController::class, 'scaleUpsert'])->name('submittals.files.page-scales.upsert');
    Route::delete('submittals/{submittal}/files/{submittalFile}/page-scales/{pageNumber}', [SubmittalPdfMarkupController::class, 'scaleDestroy'])->name('submittals.files.page-scales.destroy');
    Route::get('submittals/{submittal}/files/{submittalFile}/markups/export', [SubmittalPdfMarkupController::class, 'export'])->name('submittals.files.markups.export');
    Route::get('submittals/{submittal}/notes', [SubmittalNoteController::class, 'index'])->name('submittals.notes.index');
    Route::post('submittals/{submittal}/notes', [SubmittalNoteController::class, 'store'])->name('submittals.notes.store');

    // Fab Queue
    Route::get('fab-queue', [FabQueueController::class, 'index'])->name('fab-queue.index');
    Route::get('fab-queue/{fab_queue}', [FabQueueController::class, 'show'])->name('fab-queue.show');
    Route::post('fab-queue/{fab_queue}/assign', [FabQueueController::class, 'assign'])->name('fab-queue.assign');
    Route::post('fab-queue/{fab_queue}/complete', [FabQueueController::class, 'complete'])->name('fab-queue.complete');
    Route::put('fab-queue/{fab_queue}/notes', [FabQueueController::class, 'updateNotes'])->name('fab-queue.update-notes');
});
