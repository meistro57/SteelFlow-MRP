<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use App\Models\VendorInvoice;
use App\Services\Procurement\ThreeWayMatchService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('invoice_number')
                ->required()
                ->maxLength(255),
            TextInput::make('amount')
                ->required()
                ->numeric()
                ->prefix('$'),
            DatePicker::make('invoice_date')
                ->required()
                ->default(now()),
            Textarea::make('notes')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('match_status')
                    ->badge()
                    ->colors([
                        'success' => 'matched',
                        'danger' => 'variance',
                        'warning' => 'unmatched',
                    ])
                    ->sortable(),
            ])
            ->filters([

            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->after(function (VendorInvoice $record) {
                        $service = app(ThreeWayMatchService::class);
                        $service->performMatch($record);
                    }),
            ])
            ->actions([
                Actions\Action::make('perform_match')
                    ->label('Match')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->action(function (VendorInvoice $record) {
                        $service = app(ThreeWayMatchService::class);
                        $status = $service->performMatch($record);

                        Notification::make()
                            ->success()
                            ->title('Matching Processed')
                            ->body('Result: '.strtoupper($status))
                            ->send();
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
