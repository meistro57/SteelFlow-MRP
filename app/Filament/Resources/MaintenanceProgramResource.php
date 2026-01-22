<?php

// MaintenanceProgramResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceProgramResource\Pages;
use App\Models\MaintenanceProgram;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
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

class MaintenanceProgramResource extends Resource
{
    protected static ?string $model = MaintenanceProgram::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static UnitEnum|string|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Program Details')
                ->schema([
                    Select::make('equipment_id')
                        ->relationship('equipment', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('interval_value')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Select::make('interval_unit')
                        ->options([
                            'days' => 'Days',
                            'hours' => 'Hours',
                            'kilometres' => 'Kilometres',
                            'cycles' => 'Cycles',
                            'loads' => 'Loads',
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
                    TextInput::make('estimated_hours')
                        ->numeric()
                        ->step(0.25),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columns(3),

            Section::make('Standards & References')
                ->schema([
                    TextInput::make('procedure_reference')
                        ->maxLength(255)
                        ->placeholder('Manual section, SOP, or checklist reference'),
                    Textarea::make('safety_standards')
                        ->placeholder('e.g. LOLER, PUWER, ISO 9001')
                        ->columnSpanFull(),
                    Textarea::make('checklist')
                        ->columnSpanFull(),
                    Textarea::make('required_parts')
                        ->columnSpanFull(),
                ]),

            Section::make('Program Tasks')
                ->schema([
                    Repeater::make('programTasks')
                        ->relationship()
                        ->schema([
                            TextInput::make('sequence')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                            TextInput::make('description')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Toggle::make('is_mandatory')
                                ->default(true),
                            TextInput::make('expected_minutes')
                                ->numeric()
                                ->minValue(1)
                                ->label('Expected Minutes'),
                            Textarea::make('reference_notes')
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->defaultItems(0)
                        ->addActionLabel('Add Task')
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interval_value')
                    ->label('Interval')
                    ->formatStateUsing(fn (MaintenanceProgram $record): string =>
                        $record->interval_value.' '.$record->interval_unit),
                Tables\Columns\TextColumn::make('priority')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
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
            ->defaultSort('title');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenancePrograms::route('/'),
            'create' => Pages\CreateMaintenanceProgram::route('/create'),
            'edit' => Pages\EditMaintenanceProgram::route('/{record}/edit'),
        ];
    }
}
