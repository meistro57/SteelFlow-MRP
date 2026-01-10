<?php

declare(strict_types=1);

namespace App\Filament\Resources\LoadResource\RelationManagers;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('quantity')->numeric()->required()->default(1),
            TextInput::make('weight_lbs')->numeric()->suffix('lbs'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assemblyInstance.assembly.mark')->label('Assembly Mark'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('weight_lbs')->label('Weight')->suffix(' lbs'),
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
