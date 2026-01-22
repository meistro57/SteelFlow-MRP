<?php

// MaintenanceUsageLogResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceUsageLogResource\Pages;
use App\Models\MaintenanceUsageLog;
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

class MaintenanceUsageLogResource extends Resource
{
    protected static ?string $model = MaintenanceUsageLog::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Usage Log')
                ->schema([
                    Select::make('equipment_id')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->required(),
                    DatePicker::make('usage_date')
                        ->required(),
                    TextInput::make('usage_amount')
                        ->numeric()
                        ->step(0.01)
                        ->required(),
                    TextInput::make('meter_reading')
                        ->numeric()
                        ->step(0.01),
                    Select::make('logged_by')
                        ->relationship('loggedBy', 'name')
                        ->searchable(),
                    Textarea::make('notes')
                        ->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usage_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_amount')
                    ->sortable(),
                Tables\Columns\TextColumn::make('meter_reading'),
                Tables\Columns\TextColumn::make('loggedBy.name')
                    ->label('Logged By'),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('usage_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceUsageLogs::route('/'),
            'create' => Pages\CreateMaintenanceUsageLog::route('/create'),
            'edit' => Pages\EditMaintenanceUsageLog::route('/{record}/edit'),
        ];
    }
}
