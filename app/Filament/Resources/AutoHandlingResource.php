<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutoHandlingResource\Pages;
use Modules\UPF\Models\AutoHandling;
use Modules\UPF\Models\MaterialType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AutoHandlingResource extends Resource
{
    protected static ?string $model = AutoHandling::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Material Setup';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Auto Handling Details')
                ->schema([
                    Select::make('material_type_id')
                        ->relationship('materialType', 'type')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) => 
                            $set('type', MaterialType::find($state)?->type)
                        ),
                    TextInput::make('type')->disabled()->dehydrated(true),
                    TextInput::make('handling_code')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('handling_name')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('description')
                        ->maxLength(100),
                    TextInput::make('cost_per_ton')->numeric()->prefix('$'),
                    TextInput::make('cost_per_foot')->numeric()->prefix('$'),
                    TextInput::make('cost_per_piece')->numeric()->prefix('$'),
                    TextInput::make('minimum_charge')->numeric()->prefix('$'),
                    TextInput::make('pkey')->label('Legacy PKEY')->disabled()->placeholder('Automatic'),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('handling_code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('cost_per_ton')->label('Cost/Ton')->money('USD'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
            'index' => Pages\ListAutoHandlings::route('/'),
            'create' => Pages\CreateAutoHandling::route('/create'),
            'edit' => Pages\EditAutoHandling::route('/{record}/edit'),
        ];
    }
}
