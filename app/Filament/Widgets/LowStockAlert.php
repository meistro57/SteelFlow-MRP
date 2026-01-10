<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RawMaterial;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Materials';

    protected int|string|array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RawMaterial::query()
                    ->where('quantity_on_hand', '<', 5)
                    ->orderBy('quantity_on_hand'),
            )
            ->columns([
                Tables\Columns\TextColumn::make('material_type')
                    ->badge(),

                Tables\Columns\TextColumn::make('size_description')
                    ->label('Size')
                    ->limit(20),

                Tables\Columns\TextColumn::make('grade'),

                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('Qty')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('location'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('All materials stocked')
            ->emptyStateDescription('No materials are currently low in stock.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function getSort(): int
    {
        return 3;
    }
}
