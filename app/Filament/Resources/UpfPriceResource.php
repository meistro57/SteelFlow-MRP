<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UpfPriceResource\Pages;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\UPF\Models\MaterialGrade;
use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\UpfPrice;

class UpfPriceResource extends Resource
{
    protected static ?string $model = UpfPrice::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static \UnitEnum|string|null $navigationGroup = 'Material Setup';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'UPF Price Master';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Core Material Details')
                ->schema([
                    Select::make('material_type_id')
                        ->relationship('materialType', 'type')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $model = MaterialType::find($state);
                            if ($model instanceof MaterialType) {
                                $set('type', $model->type);
                            }
                        }),
                    Select::make('material_grade_id')
                        ->relationship('materialGrade', 'grade_name', fn ($query, $get) => $query->where('material_type_id', $get('material_type_id')),
                        )
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $model = MaterialGrade::find($state);
                            if ($model instanceof MaterialGrade) {
                                $set('grade_name', $model->grade_name);
                            }
                        }),
                    TextInput::make('type')->disabled()->dehydrated(true),
                    TextInput::make('grade_name')->disabled()->dehydrated(true),
                    TextInput::make('size')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('size_description')
                        ->maxLength(200),
                ])->columns(2),

            Section::make('Keys & Sequencing')
                ->schema([
                    TextInput::make('pkey')->label('Legacy PKEY')->disabled()->placeholder('Automatic'),
                    TextInput::make('filekey')->label('File Key')->disabled()->placeholder('Automatic'),
                    TextInput::make('orderkey')->label('Order Key')->disabled()->placeholder('Automatic'),
                ])->columns(3),

            Section::make('Pricing & Units')
                ->schema([
                    TextInput::make('unit_price')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Select::make('price_unit')
                        ->options([
                            'LB' => 'Per LB',
                            'FT' => 'Per Foot',
                            'EA' => 'Each',
                            'PC' => 'Per Piece',
                            'TON' => 'Per Ton',
                        ])
                        ->default('LB'),
                    TextInput::make('minimum_charge')
                        ->numeric()
                        ->prefix('$'),
                ])->columns(3),

            Section::make('Dimensions & Weights')
                ->schema([
                    TextInput::make('nominal_thickness')->numeric()->step(0.0001),
                    TextInput::make('nominal_width')->numeric()->step(0.0001),
                    TextInput::make('nominal_length')->numeric()->step(0.0001),
                    TextInput::make('weight_per_foot')->numeric()->step(0.0001),
                    TextInput::make('weight_per_unit')->numeric()->step(0.0001),
                ])->columns(2),

            Section::make('Status & Controls')
                ->schema([
                    Toggle::make('is_active')->default(true),
                    Toggle::make('is_stocked')->default(false),
                    Toggle::make('allow_fabrication')->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->sortable(),
                Tables\Columns\TextColumn::make('size')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('grade_name')->label('Grade')->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_unit')->sortable(),
                Tables\Columns\TextColumn::make('filekey')->label('FileKey')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(fn () => MaterialType::pluck('type', 'type')->toArray()),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('filekey', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUpfPrices::route('/'),
            'create' => Pages\CreateUpfPrice::route('/create'),
            'edit' => Pages\EditUpfPrice::route('/{record}/edit'),
        ];
    }
}
