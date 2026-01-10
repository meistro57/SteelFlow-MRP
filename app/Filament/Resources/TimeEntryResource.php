<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TimeEntryResource\Pages;
use App\Models\TimeEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Production';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Labor Entry')
                ->schema([
                    Select::make('employee_id')
                        ->relationship('employee', 'name')
                        ->required()
                        ->searchable(),
                    Select::make('work_area_id')
                        ->relationship('workArea', 'name')
                        ->required(),
                    TextInput::make('hours')
                        ->numeric()
                        ->required()
                        ->minValue(0.1),
                    TextInput::make('quantity_completed')
                        ->numeric()
                        ->default(1),
                    DateTimePicker::make('start_time'),
                    DateTimePicker::make('end_time'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('workArea.name')->sortable(),
                Tables\Columns\TextColumn::make('hours')->numeric(decimalPlaces: 2)->sortable(),
                Tables\Columns\TextColumn::make('quantity_completed')->label('Qty')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee')->relationship('employee', 'name'),
                Tables\Filters\SelectFilter::make('workArea')->relationship('workArea', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimeEntries::route('/'),
            'create' => Pages\CreateTimeEntry::route('/create'),
            'edit' => Pages\EditTimeEntry::route('/{record}/edit'),
        ];
    }
}
