<?php

// MaintenanceEquipmentCategoryResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceEquipmentCategoryResource\Pages;
use App\Models\MaintenanceEquipmentCategory;
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
use UnitEnum;

class MaintenanceEquipmentCategoryResource extends Resource
{
    protected static ?string $model = MaintenanceEquipmentCategory::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category Details')
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->maxLength(30)
                        ->unique(ignoreRecord: true),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('default_criticality')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'critical' => 'Critical',
                        ])
                        ->required(),
                    Select::make('default_usage_unit')
                        ->options([
                            'hours' => 'Hours',
                            'kilometres' => 'Kilometres',
                            'cycles' => 'Cycles',
                            'loads' => 'Loads',
                        ])
                        ->required(),
                    TextInput::make('default_interval_value')
                        ->numeric()
                        ->minValue(1)
                        ->label('Default Interval Value'),
                    Select::make('default_interval_unit')
                        ->options([
                            'days' => 'Days',
                            'hours' => 'Hours',
                            'kilometres' => 'Kilometres',
                            'cycles' => 'Cycles',
                            'loads' => 'Loads',
                        ])
                        ->label('Default Interval Unit'),
                    Textarea::make('description')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_criticality')
                    ->badge(),
                Tables\Columns\TextColumn::make('default_usage_unit')
                    ->badge(),
                Tables\Columns\TextColumn::make('default_interval_value')
                    ->label('Interval')
                    ->formatStateUsing(fn (MaintenanceEquipmentCategory $record): string => $record->default_interval_value
                        ? $record->default_interval_value.' '.$record->default_interval_unit
                        : '—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListMaintenanceEquipmentCategories::route('/'),
            'create' => Pages\CreateMaintenanceEquipmentCategory::route('/create'),
            'edit' => Pages\EditMaintenanceEquipmentCategory::route('/{record}/edit'),
        ];
    }
}
