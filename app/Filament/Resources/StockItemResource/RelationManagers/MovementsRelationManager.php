<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockItemResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $recordTitleAttribute = 'movement_type';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('movement_type')
                ->required(),
            TextInput::make('quantity')
                ->numeric()
                ->required(),
            TextInput::make('from_status'),
            TextInput::make('to_status'),
            TextInput::make('from_area'),
            TextInput::make('to_area'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('movement_type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('from_status')
                    ->label('Old Status'),
                Tables\Columns\TextColumn::make('to_status')
                    ->label('New Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('User'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
