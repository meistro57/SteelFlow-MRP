<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DrawingsRelationManager extends RelationManager
{
    protected static string $relationship = 'drawings';

    protected static ?string $recordTitleAttribute = 'number';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')
                ->required()
                ->maxLength(50),
            TextInput::make('revision')
                ->maxLength(10)
                ->default('0'),
            TextInput::make('title')
                ->maxLength(255),
            FileUpload::make('file_path')
                ->label('Drawing File')
                ->directory('drawings')
                ->visibility('public'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revision'),
                Tables\Columns\TextColumn::make('title')
                    ->limit(30),
                Tables\Columns\IconColumn::make('has_file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->file_path)),
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
