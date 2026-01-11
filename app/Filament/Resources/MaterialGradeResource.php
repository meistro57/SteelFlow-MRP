<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialGradeResource\Pages;
use Modules\UPF\Models\MaterialGrade;
use Modules\UPF\Models\MaterialType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialGradeResource extends Resource
{
    protected static ?string $model = MaterialGrade::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'Material Setup';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Material Grade Details')
                ->schema([
                    Select::make('material_type_id')
                        ->label('Material Type')
                        ->relationship('materialType', 'type')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => 
                            $set('type', MaterialType::find($state)?->type)
                        ),
                    TextInput::make('type')
                        ->disabled()
                        ->dehydrated(true),
                    TextInput::make('grade_name')
                        ->label('Grade Name')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('description')
                        ->maxLength(200),
                    TextInput::make('pkey')
                        ->label('Legacy PKEY')
                        ->disabled()
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
                Tables\Columns\TextColumn::make('materialType.type')->label('Type')->sortable(),
                Tables\Columns\TextColumn::make('grade_name')->label('Grade')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterialGrades::route('/'),
            'create' => Pages\CreateMaterialGrade::route('/create'),
            'edit' => Pages\EditMaterialGrade::route('/{record}/edit'),
        ];
    }
}
