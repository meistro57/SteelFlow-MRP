<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AssembliesRelationManager extends RelationManager
{
    protected static string $relationship = 'assemblies';

    protected static ?string $recordTitleAttribute = 'mark';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('mark')
                ->required()
                ->maxLength(50),
            TextInput::make('description')
                ->maxLength(255),
            TextInput::make('quantity')
                ->numeric()
                ->required()
                ->default(1),
            TextInput::make('weight_each_lbs')
                ->numeric()
                ->suffix('lbs'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mark')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_weight_lbs')
                    ->label('Weight (lbs)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' lbs'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
