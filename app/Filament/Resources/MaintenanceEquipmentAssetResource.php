<?php

// MaintenanceEquipmentAssetResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceEquipmentAssetResource\Pages;
use App\Models\MaintenanceEquipmentAsset;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceEquipmentAssetResource extends Resource
{
    protected static ?string $model = MaintenanceEquipmentAsset::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Asset Overview')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('asset_tag')
                        ->maxLength(100)
                        ->unique(ignoreRecord: true),
                    TextInput::make('serial_number')
                        ->maxLength(100),
                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                            'in_repair' => 'In Repair',
                            'out_of_service' => 'Out of Service',
                            'decommissioned' => 'Decommissioned',
                            'on_hire' => 'On Hire',
                        ])
                        ->required(),
                    Select::make('criticality')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'critical' => 'Critical',
                        ])
                        ->required(),
                    Select::make('usage_unit')
                        ->options([
                            'hours' => 'Hours',
                            'kilometres' => 'Kilometres',
                            'cycles' => 'Cycles',
                            'loads' => 'Loads',
                        ])
                        ->required(),
                    TextInput::make('current_usage')
                        ->numeric()
                        ->step(0.01)
                        ->required(),
                    TextInput::make('next_service_due_usage')
                        ->numeric()
                        ->step(0.01),
                    DatePicker::make('next_service_due_date'),
                ])->columns(3),

            Section::make('Ownership & Location')
                ->schema([
                    TextInput::make('location')
                        ->maxLength(255),
                    TextInput::make('department')
                        ->maxLength(255),
                    Select::make('responsible_user_id')
                        ->relationship('responsibleUser', 'name')
                        ->searchable(),
                ])->columns(3),

            Section::make('Asset Details')
                ->schema([
                    TextInput::make('manufacturer')
                        ->maxLength(100),
                    TextInput::make('model')
                        ->maxLength(100),
                    TextInput::make('year')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue((int) date('Y') + 1),
                    DatePicker::make('purchase_date'),
                    DatePicker::make('in_service_date'),
                    DatePicker::make('warranty_expiry'),
                    DatePicker::make('last_inspection_date'),
                    Textarea::make('notes')
                        ->columnSpanFull(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('asset_tag')
                    ->label('Asset Tag')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('criticality')
                    ->badge(),
                Tables\Columns\TextColumn::make('current_usage')
                    ->label('Usage')
                    ->formatStateUsing(fn (MaintenanceEquipmentAsset $record): string =>
                        $record->current_usage.' '.$record->usage_unit),
                Tables\Columns\TextColumn::make('next_service_due_date')
                    ->date()
                    ->label('Next Service'),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'in_repair' => 'In Repair',
                        'out_of_service' => 'Out of Service',
                        'decommissioned' => 'Decommissioned',
                        'on_hire' => 'On Hire',
                    ]),
                Tables\Filters\SelectFilter::make('criticality')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
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
            'index' => Pages\ListMaintenanceEquipmentAssets::route('/'),
            'create' => Pages\CreateMaintenanceEquipmentAsset::route('/create'),
            'edit' => Pages\EditMaintenanceEquipmentAsset::route('/{record}/edit'),
        ];
    }
}
