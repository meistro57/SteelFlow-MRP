<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    public function index()
    {
        return Inertia::render('Reports/Index', [
            'metrics' => $this->reportingService->getDashboardMetrics()
        ]);
    }

    public function projectBom(Project $project)
    {
        $data = $this->reportingService->getProjectBOMReport($project);

        return Inertia::render('Reports/ProjectBom', $data);
    }

    public function inventory()
    {
        $data = $this->reportingService->getInventoryReport();

        return Inertia::render('Reports/Inventory', $data);
    }
}
