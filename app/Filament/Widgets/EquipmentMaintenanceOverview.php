<?php

// EquipmentMaintenanceOverview.php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MaintenanceComplianceRecord;
use App\Models\MaintenanceEquipmentAsset;
use App\Models\MaintenanceInspection;
use App\Models\MaintenancePart;
use App\Models\MaintenanceWorkOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EquipmentMaintenanceOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Equipment Maintenance Snapshot';

    protected function getStats(): array
    {
        $today = Carbon::today();

        $openWorkOrders = MaintenanceWorkOrder::query()
            ->whereIn('status', ['open', 'scheduled', 'in_progress', 'blocked'])
            ->count();

        $overdueInspections = MaintenanceInspection::query()
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<', $today)
            ->count();

        $lowStockParts = MaintenancePart::query()
            ->whereNotNull('reorder_point')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_point')
            ->count();

        $complianceOverdue = MaintenanceComplianceRecord::query()
            ->whereNotNull('next_audit_due')
            ->whereDate('next_audit_due', '<', $today)
            ->count();

        $activeAssets = MaintenanceEquipmentAsset::query()
            ->where('status', 'active')
            ->count();

        return [
            Stat::make('Active Assets', $activeAssets)
                ->description('Operational equipment in service')
                ->color('success'),
            Stat::make('Open Work Orders', $openWorkOrders)
                ->description('Awaiting completion or scheduling')
                ->color('warning'),
            Stat::make('Overdue Inspections', $overdueInspections)
                ->description('Inspections past due')
                ->color('danger'),
            Stat::make('Low Stock Parts', $lowStockParts)
                ->description('Below reorder point')
                ->color('warning'),
            Stat::make('Compliance Overdue', $complianceOverdue)
                ->description('Audits past due')
                ->color('danger'),
        ];
    }

    public static function getSort(): int
    {
        return 4;
    }
}
