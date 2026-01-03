<?php

namespace App\Services;

use App\Models\Assembly;
use App\Models\Project;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Get summary metrics for the main dashboard.
     */
    public function getDashboardMetrics(): array
    {
        return [
            'active_projects' => Project::whereIn('status', ['active', 'production'])->count(),
            'total_weight_lbs' => Assembly::sum('total_weight_lbs'),
            'production_completion_percentage' => $this->calculateProductionProgress(),
            'ready_to_ship_pieces' => Assembly::whereHas('instances', function ($q) {
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
            'total_items' => StockItem::where('status', '!=', 'used')->count(),
            'valuation' => StockItem::where('status', '!=', 'used')
                ->selectRaw('SUM(length * cost_per_unit) as total_value')
                ->value('total_value'),
            'by_type' => StockItem::where('status', '!=', 'used')
                ->select('type', DB::raw('count(*) as count'), DB::raw('sum(length) as total_length'))
                ->groupBy('type')
                ->get(),
        ];
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
