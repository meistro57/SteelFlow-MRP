<?php

// MaintenanceFaultReportResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceFaultReportResource\Pages;
use App\Models\MaintenanceFaultReport;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceFaultReportResource extends Resource
{
    protected static ?string $model = MaintenanceFaultReport::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Fault Report')
                ->schema([
                    Select::make('equipment_id')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->required(),
                    Select::make('reported_by')
                        ->relationship('reportedBy', 'name')
                        ->searchable(),
                    DateTimePicker::make('reported_at')
                        ->required(),
                    Select::make('severity')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'critical' => 'Critical',
                        ])
                        ->required(),
                    Select::make('status')
                        ->options([
                            'open' => 'Open',
                            'in_progress' => 'In Progress',
                            'resolved' => 'Resolved',
                            'closed' => 'Closed',
                        ])
                        ->required(),
                    Textarea::make('description')
                        ->required()
                        ->columnSpanFull(),
                    Textarea::make('immediate_action')
                        ->columnSpanFull(),
                    Textarea::make('resolution_notes')
                        ->columnSpanFull(),
                    DateTimePicker::make('resolved_at'),
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
                Tables\Columns\TextColumn::make('reported_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('severity')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('reportedBy.name')
                    ->label('Reported By'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reported_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceFaultReports::route('/'),
            'create' => Pages\CreateMaintenanceFaultReport::route('/create'),
            'edit' => Pages\EditMaintenanceFaultReport::route('/{record}/edit'),
        ];
    }
}
