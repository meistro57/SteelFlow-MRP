<?php

declare(strict_types=1);

namespace App\Filament\Resources\NestingResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BarsRelationManager extends RelationManager
{
    protected static string $relationship = 'bars';

    protected static ?string $recordTitleAttribute = 'bar_number';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('bar_number')->required(),
            TextInput::make('length')->numeric()->required()->suffix('in'),
            TextInput::make('used_length')->numeric()->required()->default(0),
            TextInput::make('remnant_length')->numeric()->required()->default(0),
            Toggle::make('is_cut'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bar_number')->sortable(),
                Tables\Columns\TextColumn::make('stockItem.stock_id')->label('Stock ID'),
                Tables\Columns\TextColumn::make('length')->suffix(' in'),
                Tables\Columns\TextColumn::make('used_length')->suffix(' in'),
                Tables\Columns\TextColumn::make('remnant_length')->label('Remnant')->suffix(' in'),
                Tables\Columns\IconColumn::make('is_cut')->boolean(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }
}
