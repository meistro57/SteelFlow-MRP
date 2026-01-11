<?php

// app/Services/ReportingService.php

namespace App\Services;

use App\Models\Assembly;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\StockItem;

class ReportingService
{
    /**
     * Get report module overview data.
     */
    public function getReportsOverview(): array
    {
        return [
            'metrics' => $this->getDashboardMetrics(),
            'inventorySnapshot' => $this->getInventoryReport(),
            'projects' => Project::with('customer')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get(),
        ];
    }

    /**
     * Get summary metrics for the main dashboard.
     */
    public function getDashboardMetrics(): array
    {
        return [
            'active_projects' => Project::whereIn('status', ['active', 'production'])->count(),
            'total_weight_lbs' => Assembly::sum('total_weight_lbs'),
            'production_completion_percentage' => $this->calculateProductionProgress(),
            'ready_to_ship_pieces' => Assembly::whereHas('instances', function ($q): void {
                $q->where('status', 'complete');
            })->count(),
        ];
    }

    /**
     * Generate a BOM report dataset for a project.
     */
    public function getProjectBOMReport(Project $project): array
    {
        return [
            'project' => $project,
            'assemblies' => $project->assemblies()->with('parts.material')->get(),
        ];
    }

    /**
     * Get inventory valuation and stock levels.
     */
    public function getInventoryReport(): array
    {
        return [
            'totalItems' => (int) StockItem::where('status', '!=', 'used')->sum('quantity'),
            'valuation' => (float) StockItem::where('status', '!=', 'used')
                ->selectRaw('SUM(quantity * cost_per_unit) as total_value')
                ->value('total_value'),
            'byType' => StockItem::where('status', '!=', 'used')
                ->select('type', DB::raw('SUM(quantity) as count'), DB::raw('SUM(quantity * length) as total_length_in'))
                ->groupBy('type')
                ->get()
                ->map(function (StockItem $item) {
                    $item->total_length = (float) $item->total_length_in / 12; // Convert to feet for the report snapshot

                    return $item;
                }),
        ];
    }

    /**
     * Get detailed inventory items for export.
     */
    public function getInventoryExportData(): \Illuminate\Support\Collection
    {
        return StockItem::with('reservedProject')
            ->where('status', '!=', 'used')
            ->orderBy('type')
            ->orderBy('size')
            ->get();
    }

    protected function calculateProductionProgress(): float
    {
        $total = DB::table('part_work_areas')->count();
        if ($total === 0) {
            return 0;
        }

        $completed = DB::table('part_work_areas')->where('status', 'complete')->count();

        return round(($completed / $total) * 100, 2);
    }
}
