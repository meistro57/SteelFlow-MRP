<?php

declare(strict_types=1);

namespace App\Filament\Resources\FabJobResource\RelationManagers;

use App\Enums\PartStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    protected static ?string $recordTitleAttribute = 'mark_number';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('mark_number')
                ->required()
                ->maxLength(30),

            TextInput::make('description')
                ->maxLength(255),

            TextInput::make('quantity')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),

            TextInput::make('material_grade')
                ->default('A36')
                ->maxLength(30),

            TextInput::make('weight_each')
                ->numeric()
                ->suffix('lbs'),

            Select::make('status')
                ->options(PartStatus::class)
                ->default(PartStatus::Pending)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mark_number')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),

                Tables\Columns\TextColumn::make('material_grade'),

                Tables\Columns\TextColumn::make('weight_each')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' lbs'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(PartStatus::class),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->options(PartStatus::class)
                                ->required(),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
