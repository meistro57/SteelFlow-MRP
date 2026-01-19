<?php

namespace Modules\PdfCenter\Providers;

use Illuminate\Support\ServiceProvider;

class PdfCenterServiceProvider extends ServiceProvider
{
    protected string $name = 'PdfCenter';

    protected string $nameLower = 'pdfcenter';

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->nameLower);
    }

    public function register(): void {}
}
