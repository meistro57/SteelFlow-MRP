<?php

// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService,
    ) {}

    public function index()
    {
        return Inertia::render('Reports/Index', $this->reportingService->getReportsOverview());
    }

    public function showMainDashboard()
    {
        return Inertia::render('Dashboard', [
            'metrics' => $this->reportingService->getDashboardMetrics(),
            'repositoryUrl' => 'https://github.com/meistro57/SteelFlow-MRP/issues/new',
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

    public function inventoryCsv()
    {
        $items = $this->reportingService->getInventoryExportData();
        $filename = 'inventory_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Stock ID', 'Type', 'Size', 'Grade', 'Length (ft)', 'Quantity', 'Status', 'Location', 'Heat Number', 'PO Number', 'Reserved Project', 'Unit Cost', 'Total Cost']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->stock_id,
                    $item->type,
                    $item->size,
                    $item->grade,
                    $this->formatLength($item->length),
                    $item->quantity,
                    $item->status,
                    $item->stock_area,
                    $item->heat_number,
                    $item->po_number,
                    $item->reservedProject->job_number ?? '',
                    $item->cost_per_unit,
                    $item->cost_per_unit * $item->quantity,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function inventoryPdf()
    {
        $summary = $this->reportingService->getInventoryReport();
        $items = $this->reportingService->getInventoryExportData();

        $pdf = Pdf::loadView('reports.inventory', [
            'summary' => $summary,
            'items' => $items,
        ]);

        return $pdf->download('inventory_'.date('Y-m-d').'.pdf');
    }

    public function projectBomCsv(Project $project)
    {
        $project->load('assemblies.parts');
        $filename = 'bom_'.$project->job_number.'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($project): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Assembly Mark', 'Assembly Description', 'Assembly Qty', 'Part Mark', 'Part Qty', 'Material', 'Size', 'Length (ft)', 'Weight Each (lbs)', 'Total Weight (lbs)']);

            foreach ($project->assemblies as $assembly) {
                foreach ($assembly->parts as $part) {
                    fputcsv($file, [
                        $assembly->mark,
                        $assembly->description,
                        $assembly->quantity,
                        $part->part_mark,
                        $part->quantity,
                        $part->grade,
                        $part->size_imperial,
                        $this->formatLength($part->length),
                        $part->weight_each_lbs,
                        $part->weight_each_lbs * $part->quantity * $assembly->quantity,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function projectBomPdf(Project $project)
    {
        $project->load('assemblies.parts.material');

        $pdf = Pdf::loadView('reports.bom', [
            'project' => $project,
        ]);

        return $pdf->download('bom_'.$project->job_number.'_'.date('Y-m-d').'.pdf');
    }

    private function formatLength($length)
    {
        if (! $length) {
            return '-';
        }
        $feet = floor($length);
        $inches = round(($length - $feet) * 12);
        if ($inches == 12) {
            $feet++;
            $inches = 0;
        }

        return $inches > 0 ? "{$feet}' {$inches}\"" : "{$feet}'";
    }

    /**
     * Display production summary report.
     */
    public function production()
    {
        $filters = request()->only(['start_date', 'end_date']);
        $data = $this->reportingService->getProductionReport($filters);

        return Inertia::render('Reports/Production', $data);
    }

    /**
     * Display labor efficiency report.
     */
    public function laborEfficiency()
    {
        $filters = request()->only(['start_date', 'end_date']);
        $data = $this->reportingService->getLaborEfficiencyReport($filters);

        return Inertia::render('Reports/LaborEfficiency', $data);
    }

    /**
     * Display batch completion timeline report.
     */
    public function batchCompletion()
    {
        $data = $this->reportingService->getBatchCompletionReport();

        return Inertia::render('Reports/BatchCompletion', $data);
    }
}
