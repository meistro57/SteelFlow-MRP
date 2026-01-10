<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StockItemResource\Pages;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Inventory\Models\StockItem;

class StockItemResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Stock List';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Stock Identification')
                ->schema([
                    TextInput::make('stock_id')
                        ->label('Stock ID')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('material_id')
                        ->relationship('material', 'size_description')
                        ->searchable()
                        ->preload(),
                    TextInput::make('type'),
                    TextInput::make('size'),
                    TextInput::make('grade'),
                ])->columns(2),

            Section::make('Physical Properties')
                ->schema([
                    TextInput::make('length')
                        ->numeric()
                        ->suffix('in'),
                    TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->default(1),
                    TextInput::make('stock_area')
                        ->label('Location/Area'),
                    Toggle::make('is_remnant')
                        ->label('Is Remnant'),
                ])->columns(2),

            Section::make('Status & Assignment')
                ->schema([
                    Select::make('status')
                        ->options([
                            'free' => 'Free',
                            'assigned' => 'Assigned',
                            'committed' => 'Committed',
                            'used' => 'Used',
                            'available' => 'Available',
                            'reserved' => 'Reserved',
                        ])
                        ->required()
                        ->default('free'),
                    Select::make('reserved_project_id')
                        ->relationship('reservedProject', 'name')
                        ->label('Reserved Project')
                        ->searchable()
                        ->preload(),
                ])->columns(2),

            Section::make('Traceability')
                ->schema([
                    TextInput::make('heat_number'),
                    TextInput::make('po_number')
                        ->label('PO Number'),
                    TextInput::make('country_of_origin'),
                    DatePicker::make('receive_date')
                        ->native(false),
                ])->columns(2),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stock_id')
                    ->label('Stock ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free', 'available' => 'success',
                        'assigned', 'reserved' => 'warning',
                        'committed' => 'primary',
                        'used' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('length')
                    ->suffix(' in')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_area')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('heat_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reservedProject.job_number')
                    ->label('Project')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'free' => 'Free',
                        'assigned' => 'Assigned',
                        'committed' => 'Committed',
                        'used' => 'Used',
                    ]),
                Tables\Filters\SelectFilter::make('stock_area')
                    ->label('Location')
                    ->multiple()
                    ->options(fn () => StockItem::whereNotNull('stock_area')->distinct()->pluck('stock_area', 'stock_area')->toArray()),
                Tables\Filters\TernaryFilter::make('is_remnant'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            StockItemResource\RelationManagers\MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockItems::route('/'),
            'create' => Pages\CreateStockItem::route('/create'),
            'edit' => Pages\EditStockItem::route('/{record}/edit'),
        ];
    }
}
