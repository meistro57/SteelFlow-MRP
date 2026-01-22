<?php

// MaintenanceComplianceRecordResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceComplianceRecordResource\Pages;
use App\Models\MaintenanceComplianceRecord;
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

class MaintenanceComplianceRecordResource extends Resource
{
    protected static ?string $model = MaintenanceComplianceRecord::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Compliance Record')
                ->schema([
                    Select::make('equipment_id')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('standard')
                        ->required()
                        ->maxLength(255),
                    Select::make('status')
                        ->options([
                            'compliant' => 'Compliant',
                            'non_compliant' => 'Non-compliant',
                            'pending' => 'Pending',
                            'expired' => 'Expired',
                        ])
                        ->required(),
                    DatePicker::make('last_audit_date'),
                    DatePicker::make('next_audit_due'),
                    Select::make('audited_by')
                        ->relationship('auditedBy', 'name')
                        ->searchable(),
                    Textarea::make('evidence_location')
                        ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('standard')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('last_audit_date')
                    ->date(),
                Tables\Columns\TextColumn::make('next_audit_due')
                    ->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'compliant' => 'Compliant',
                        'non_compliant' => 'Non-compliant',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
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
            ->defaultSort('next_audit_due');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceComplianceRecords::route('/'),
            'create' => Pages\CreateMaintenanceComplianceRecord::route('/create'),
            'edit' => Pages\EditMaintenanceComplianceRecord::route('/{record}/edit'),
        ];
    }
}
