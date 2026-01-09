<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PartStatus;
use App\Models\FabPart;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingPartsStats extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingCount = FabPart::where('status', PartStatus::Pending)->count();
        $cuttingCount = FabPart::where('status', PartStatus::Cutting)->count();
        $fabricationCount = FabPart::where('status', PartStatus::Fabrication)->count();
        $completeToday = FabPart::where('status', PartStatus::Complete)
            ->whereDate('updated_at', today())
            ->count();

        return [
            Stat::make('Pending Parts', $pendingCount)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, $pendingCount]),

            Stat::make('In Cutting', $cuttingCount)
                ->description('Currently being cut')
                ->descriptionIcon('heroicon-m-scissors')
                ->color('info'),

            Stat::make('In Fabrication', $fabricationCount)
                ->description('Being fabricated')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('primary'),

            Stat::make('Completed Today', $completeToday)
                ->description('Parts finished today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    public static function getSort(): int
    {
        return 2;
    }
}
