<?php

// MaintenancePartResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenancePartResource\Pages;
use App\Models\MaintenancePart;
use BackedEnum;
use Filament\Actions;
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
use UnitEnum;

class MaintenancePartResource extends Resource
{
    protected static ?string $model = MaintenancePart::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Part Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('part_number')
                        ->maxLength(100),
                    TextInput::make('category')
                        ->maxLength(100),
                    Select::make('vendor_id')
                        ->relationship('vendor', 'name')
                        ->searchable(),
                    TextInput::make('unit_cost')
                        ->numeric()
                        ->step(0.01)
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columns(3),

            Section::make('Inventory')
                ->schema([
                    TextInput::make('quantity_on_hand')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    TextInput::make('reorder_point')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('reorder_qty')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('lead_time_days')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('inventory_location')
                        ->maxLength(255),
                    DatePicker::make('last_stocktake_at'),
                ])
                ->columns(3),

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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('On Hand')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('Reorder')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceParts::route('/'),
            'create' => Pages\CreateMaintenancePart::route('/create'),
            'edit' => Pages\EditMaintenancePart::route('/{record}/edit'),
        ];
    }
}
