<?php

// MaintenanceInspectionResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceInspectionResource\Pages;
use App\Models\MaintenanceInspection;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceInspectionResource extends Resource
{
    protected static ?string $model = MaintenanceInspection::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inspection Details')
                ->schema([
                    Select::make('equipment_id')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->required(),
                    Select::make('inspector_id')
                        ->relationship('inspector', 'name')
                        ->searchable(),
                    DatePicker::make('inspection_date')
                        ->required(),
                    Select::make('condition_rating')
                        ->options([
                            'excellent' => 'Excellent',
                            'good' => 'Good',
                            'fair' => 'Fair',
                            'poor' => 'Poor',
                            'critical' => 'Critical',
                        ])
                        ->required(),
                    Select::make('compliance_status')
                        ->options([
                            'compliant' => 'Compliant',
                            'non_compliant' => 'Non-compliant',
                            'pending' => 'Pending',
                        ])
                        ->required(),
                    DatePicker::make('next_due_date'),
                    Toggle::make('follow_up_required')
                        ->label('Follow-up Required'),
                    Textarea::make('findings')
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
                Tables\Columns\TextColumn::make('inspection_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('condition_rating')
                    ->badge(),
                Tables\Columns\TextColumn::make('compliance_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('next_due_date')
                    ->date(),
                Tables\Columns\IconColumn::make('follow_up_required')
                    ->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('inspection_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceInspections::route('/'),
            'create' => Pages\CreateMaintenanceInspection::route('/create'),
            'edit' => Pages\EditMaintenanceInspection::route('/{record}/edit'),
        ];
    }
}
