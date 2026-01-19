<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssemblyResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $recordTitleAttribute = 'part_mark';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('part_mark')
                ->required()
                ->maxLength(50),
            TextInput::make('quantity')
                ->numeric()
                ->required()
                ->default(1),
            TextInput::make('size_imperial')
                ->maxLength(100),
            TextInput::make('grade')
                ->maxLength(30),
            TextInput::make('weight_each_lbs')
                ->numeric()
                ->suffix('lbs'),
            Toggle::make('is_main_member'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('part_mark')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size_imperial')
                    ->label('Size'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('total_weight_lbs')
                    ->label('Weight (lbs)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' lbs'),
                Tables\Columns\IconColumn::make('is_main_member')
                    ->label('Main')
                    ->boolean(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
