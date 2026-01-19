<?php

// app/Filament/Resources/ProductionItemResource.php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionItemResource\Pages;
use App\Models\ProductionItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;
use UnitEnum;

class ProductionItemResource extends Resource
{
    protected static ?string $model = ProductionItem::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static UnitEnum|string|null $navigationGroup = 'Production';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),
            TextInput::make('part_number')
                ->label('Part Number')
                ->required()
                ->maxLength(255),
            Placeholder::make('status')
                ->label('Status')
                ->content(fn (?ProductionItem $record): string => $record?->displayStatus ?? 'Queued'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('part_number')
                    ->label('Part Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('displayStatus')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('advanceStatus')
                    ->label('Advance Status')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->requiresConfirmation()
                    ->disabled(fn (ProductionItem $record): bool => $record->nextStatus() === null)
                    ->action(function (ProductionItem $record): void {
                        $nextState = $record->nextStatus();

                        if ($nextState === null) {
                            Notification::make()
                                ->title('Already shipped')
                                ->warning()
                                ->send();

                            return;
                        }

                        try {
                            $record->status->transitionTo($nextState);
                            $record->save();

                            Notification::make()
                                ->title('Status advanced')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('Unable to advance status')
                                ->body('Please try again.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionItems::route('/'),
            'create' => Pages\CreateProductionItem::route('/create'),
            'edit' => Pages\EditProductionItem::route('/{record}/edit'),
        ];
    }
}
