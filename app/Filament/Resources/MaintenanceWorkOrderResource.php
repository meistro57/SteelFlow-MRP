<?php

// MaintenanceWorkOrderResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceWorkOrderResource\Pages;
use App\Models\MaintenanceWorkOrder;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class MaintenanceWorkOrderResource extends Resource
{
    protected static ?string $model = MaintenanceWorkOrder::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Work Order Summary')
                ->schema([
                    Select::make('equipment_id')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->required(),
                    Select::make('maintenance_program_id')
                        ->relationship('maintenanceProgram', 'title')
                        ->searchable(),
                    Select::make('fault_report_id')
                        ->relationship('faultReport', 'id')
                        ->searchable()
                        ->label('Fault Report'),
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->columnSpanFull(),
                    Select::make('status')
                        ->options([
                            'open' => 'Open',
                            'scheduled' => 'Scheduled',
                            'in_progress' => 'In Progress',
                            'blocked' => 'Blocked',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required(),
                    Select::make('priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->required(),
                ])
                ->columns(3),

            Section::make('Scheduling & Ownership')
                ->schema([
                    Select::make('requested_by')
                        ->relationship('requestedBy', 'name')
                        ->searchable(),
                    Select::make('assigned_to')
                        ->relationship('assignedTo', 'name')
                        ->searchable(),
                    DatePicker::make('scheduled_date'),
                    DatePicker::make('due_date'),
                    DateTimePicker::make('started_at'),
                    DateTimePicker::make('completed_at'),
                ])
                ->columns(3),

            Section::make('Operational Metrics')
                ->schema([
                    TextInput::make('labour_hours')
                        ->numeric()
                        ->step(0.25),
                    TextInput::make('downtime_hours')
                        ->numeric()
                        ->step(0.25),
                    TextInput::make('meter_reading')
                        ->numeric()
                        ->step(0.01),
                    TextInput::make('location')
                        ->maxLength(255),
                    Textarea::make('completion_notes')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Tasks')
                ->schema([
                    Repeater::make('tasks')
                        ->relationship()
                        ->schema([
                            TextInput::make('description')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Select::make('completed_by')
                                ->relationship('completedBy', 'name')
                                ->searchable(),
                            DateTimePicker::make('completed_at'),
                            Textarea::make('notes')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Add Task')
                        ->reorderable(),
                ])
                ->collapsible(),

            Section::make('Parts Usage')
                ->schema([
                    Repeater::make('partUsages')
                        ->relationship()
                        ->schema([
                            Select::make('part_id')
                                ->relationship('part', 'name')
                                ->searchable()
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('quantity')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                            TextInput::make('unit_cost')
                                ->numeric()
                                ->step(0.01),
                            TextInput::make('cost_total')
                                ->numeric()
                                ->step(0.01),
                            DateTimePicker::make('used_at'),
                            Textarea::make('notes')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Add Part Usage')
                        ->reorderable(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'blocked' => 'Blocked',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
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
            ->defaultSort('due_date');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceWorkOrders::route('/'),
            'create' => Pages\CreateMaintenanceWorkOrder::route('/create'),
            'edit' => Pages\EditMaintenanceWorkOrder::route('/{record}/edit'),
        ];
    }
}
