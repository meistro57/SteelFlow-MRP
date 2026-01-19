<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Inventory\Models\StockMovement;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Movement History';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Movement Details')
                ->schema([
                    Select::make('stock_item_id')
                        ->relationship('stockItem', 'stock_id')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('movement_type')
                        ->required(),
                    TextInput::make('quantity')
                        ->numeric()
                        ->required(),
                ])->columns(2),

            Section::make('State Changes')
                ->schema([
                    TextInput::make('from_status'),
                    TextInput::make('to_status'),
                    TextInput::make('from_area'),
                    TextInput::make('to_area'),
                ])->columns(2),

            Section::make('Audit')
                ->schema([
                    Select::make('created_by')
                        ->relationship('creator', 'name'),
                    Textarea::make('notes')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stockItem.stock_id')
                    ->label('Stock ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('from_status')
                    ->label('Old Status')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('to_status')
                    ->label('New Status')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('User')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->options([
                        'receive' => 'Receive',
                        'assign' => 'Assign',
                        'commit' => 'Commit',
                        'use' => 'Use',
                        'return' => 'Return',
                        'adjust' => 'Adjust',
                    ]),
                Tables\Filters\SelectFilter::make('creator')
                    ->relationship('creator', 'name')
                    ->label('User'),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'view' => Pages\ViewStockMovements::route('/{record}'),
        ];
    }
}
