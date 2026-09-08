<?php

namespace Modules\DrawingFlow\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\DrawingFlow\Models\DrawingRequest;
use Modules\DrawingFlow\Models\DrawingSubmittal;
use Modules\DrawingFlow\Models\FabQueue;

class DrawingFlowServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'DrawingFlow';

    protected string $nameLower = 'drawingflow';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // Explicit route-model bindings (soft-deleted workflow models).
        Route::model('drawing_request', DrawingRequest::class);
        Route::model('submittal', DrawingSubmittal::class);
        Route::model('fab_queue', FabQueue::class);
    }
}
