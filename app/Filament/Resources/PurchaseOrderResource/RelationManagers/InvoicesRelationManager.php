<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use App\Models\VendorInvoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Services\Procurement\ThreeWayMatchService;

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
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function (VendorInvoice $record) {
                        $service = app(ThreeWayMatchService::class);
                        $service->performMatch($record);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('perform_match')
                    ->label('Match')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->action(function (VendorInvoice $record) {
                        $service = app(ThreeWayMatchService::class);
                        $status = $service->performMatch($record);
                        
                        Notification::make()
                            ->success()
                            ->title('Matching Processed')
                            ->body("Result: " . strtoupper($status))
                            ->send();
                    }),
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
