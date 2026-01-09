<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\JobStatus;
use App\Models\FabJob;
use Filament\Widgets\ChartWidget;

class JobsStatusChart extends ChartWidget
{
    protected ?string $heading = 'Jobs by Status';

    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $data = collect(JobStatus::cases())->map(function ($status) {
            return FabJob::where('status', $status)->count();
        });

        $labels = collect(JobStatus::cases())->map(fn ($status) => $status->getLabel());

        $colors = [
            '#6b7280', // gray - estimating
            '#3b82f6', // blue - awarded
            '#f59e0b', // amber - in_progress
            '#8b5cf6', // violet - shipping
            '#22c55e', // green - complete
            '#ef4444', // red - hold
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jobs',
                    'data' => $data->toArray(),
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }

    public static function getSort(): int
    {
        return 1;
    }
}
