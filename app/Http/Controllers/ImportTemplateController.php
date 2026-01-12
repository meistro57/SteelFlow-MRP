<?php

// app/Http/Controllers/ImportTemplateController.php

namespace App\Http\Controllers;

use App\Services\Import\ImportTemplateService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportTemplateController extends Controller
{
    public function __construct(
        protected ImportTemplateService $templateService
    ) {}

    /**
     * Display list of all available import templates.
     */
    public function index(): Response
    {
        return Inertia::render('ImportTemplates/Index', [
            'templates' => $this->templateService->getAvailableTemplates(),
        ]);
    }

    /**
     * Download a specific import template.
     */
    public function download(string $type): StreamedResponse
    {
        return $this->templateService->download($type);
    }

    /**
     * Download customers import template.
     */
    public function customers(): StreamedResponse
    {
        return $this->templateService->download(ImportTemplateService::TEMPLATE_CUSTOMERS);
    }

    /**
     * Download KISS format import template.
     */
    public function kiss(): StreamedResponse
    {
        return $this->templateService->download(ImportTemplateService::TEMPLATE_KISS);
    }

    /**
     * Download XSR format import template.
     */
    public function xsr(): StreamedResponse
    {
        return $this->templateService->download(ImportTemplateService::TEMPLATE_XSR);
    }

    /**
     * Download UPF material catalog import template.
     */
    public function upf(): StreamedResponse
    {
        return $this->templateService->download(ImportTemplateService::TEMPLATE_UPF);
    }
}
