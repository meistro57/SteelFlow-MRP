<?php

declare(strict_types=1);

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Inventory\Services\InventoryService;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    protected static ?string $recordTitleAttribute = 'line_number';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('line_number')
                ->numeric()
                ->required(),
            TextInput::make('type')
                ->placeholder('Beam, Angle, Plate, etc.'),
            TextInput::make('size')
                ->placeholder('W10x26, etc.'),
            TextInput::make('grade')
                ->placeholder('A36, A992, etc.'),
            TextInput::make('length')
                ->numeric()
                ->suffix('in'),
            TextInput::make('quantity')
                ->numeric()
                ->required()
                ->default(1),
            TextInput::make('unit_price')
                ->numeric()
                ->prefix('$'),
            Textarea::make('notes')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('line_number')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('size'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Ord')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_received')
                    ->label('Recvd')
                    ->sortable()
                    ->color(fn ($record) => $record->quantity_received >= $record->quantity ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('extended_price')
                    ->money('USD'),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('receive')
                    ->label('Receive')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn ($record) => $record->quantity_received < $record->quantity)
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to Receive')
                            ->numeric()
                            ->required()
                            ->default(fn ($record) => $record->quantity - $record->quantity_received)
                            ->maxValue(fn ($record) => $record->quantity - $record->quantity_received),
                        TextInput::make('heat_number')
                            ->label('Heat Number'),
                        TextInput::make('stock_area')
                            ->label('Stock Area/Location'),
                        DatePicker::make('receive_date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        app(InventoryService::class)->receiveLine($record, $data);
                    }),
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
