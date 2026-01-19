<?php

namespace App\Filament\Exports;

use App\Models\FabPart;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class FabPartExporter extends Exporter
{
    protected static ?string $model = FabPart::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('fabJob.job_number')
                ->label('Job Number'),

            ExportColumn::make('mark_number')
                ->label('Mark Number'),

            ExportColumn::make('description')
                ->label('Description'),

            ExportColumn::make('quantity')
                ->label('Quantity'),

            ExportColumn::make('material_grade')
                ->label('Material Grade'),

            ExportColumn::make('weight_each')
                ->label('Weight Each (lbs)'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => $state?->getLabel()),

            ExportColumn::make('created_at')
                ->label('Created'),

            ExportColumn::make('updated_at')
                ->label('Updated'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your parts export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
