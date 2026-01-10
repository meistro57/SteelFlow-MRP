<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\WorkAreaResource\Pages;
use App\Models\WorkArea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WorkAreaResource extends Resource
{
    protected static ?string $model = WorkArea::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static string|\UnitEnum|null $navigationGroup = 'Production';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Work Area Details')
                ->schema([
                    Select::make('department_id')
                        ->relationship('department', 'name')
                        ->required(),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('badge_code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('department.name')->sortable(),
                Tables\Columns\TextColumn::make('badge_code')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkAreas::route('/'),
            'create' => Pages\CreateWorkArea::route('/create'),
            'edit' => Pages\EditWorkArea::route('/{record}/edit'),
        ];
    }
}
