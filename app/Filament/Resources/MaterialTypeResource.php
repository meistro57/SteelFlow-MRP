<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialTypeResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\UPF\Models\MaterialType;

class MaterialTypeResource extends Resource
{
    protected static ?string $model = MaterialType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Material Setup';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Material Type Details')
                ->schema([
                    TextInput::make('type')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    TextInput::make('title')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('description')
                        ->maxLength(200)
                        ->columnSpanFull(),
                    TextInput::make('pkey')
                        ->label('Legacy PKEY')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Automatic')
                        ->maxLength(50),
                    Toggle::make('is_active')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('material_grades_count')->counts('materialGrades')
                    ->label('Grades'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterialTypes::route('/'),
            'create' => Pages\CreateMaterialType::route('/create'),
            'edit' => Pages\EditMaterialType::route('/{record}/edit'),
        ];
    }
}
