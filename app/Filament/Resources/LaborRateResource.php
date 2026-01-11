<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaborRateResource\Pages;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\UPF\Models\LaborRate;
use Modules\UPF\Models\MaterialType;
use UnitEnum;

class LaborRateResource extends Resource
{
    protected static ?string $model = LaborRate::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static UnitEnum|string|null $navigationGroup = 'Material Setup';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Labor Operation Details')
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
                    TextInput::make('type')->disabled()->dehydrated(true),
                    TextInput::make('operation_code')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('operation_name')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('setup_time_minutes')
                        ->numeric()
                        ->label('Setup Time (Mins)'),
                    TextInput::make('run_time_per_unit')
                        ->numeric()
                        ->label('Run Time per Unit'),
                    TextInput::make('rate_per_hour')
                        ->numeric()
                        ->prefix('$'),
                    TextInput::make('rate_per_unit')
                        ->numeric()
                        ->prefix('$'),
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
                Tables\Columns\TextColumn::make('operation_code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('operation_name')->searchable(),
                Tables\Columns\TextColumn::make('rate_per_hour')->money('USD'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
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
            'index' => Pages\ListLaborRates::route('/'),
            'create' => Pages\CreateLaborRate::route('/create'),
            'edit' => Pages\EditLaborRate::route('/{record}/edit'),
        ];
    }
}
