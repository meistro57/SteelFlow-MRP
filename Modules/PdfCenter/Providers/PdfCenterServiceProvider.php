<?php

namespace Modules\PdfCenter\Providers;

use Illuminate\Support\ServiceProvider;

class PdfCenterServiceProvider extends ServiceProvider
{
    protected string $name = 'PdfCenter';

    protected string $nameLower = 'pdfcenter';

    public function boot(): void
    {
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
    }

    public function register(): void {}
}
