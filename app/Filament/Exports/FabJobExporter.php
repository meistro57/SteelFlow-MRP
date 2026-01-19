<?php

namespace App\Filament\Exports;

use App\Models\FabJob;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class FabJobExporter extends Exporter
{
    protected static ?string $model = FabJob::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('job_number')
                ->label('Job Number'),

            ExportColumn::make('customer_name')
                ->label('Customer'),

            ExportColumn::make('description')
                ->label('Description'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state?->getLabel()),

            ExportColumn::make('due_date')
                ->label('Due Date'),

            ExportColumn::make('parts_count')
                ->label('Total Parts')
                ->counts('parts'),

            ExportColumn::make('created_at')
                ->label('Created'),

            ExportColumn::make('updated_at')
                ->label('Updated'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your job export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
