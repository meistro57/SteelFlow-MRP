<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditEntryResource\Pages;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\AuditLog\Models\AuditEntry;
use UnitEnum;

class AuditEntryResource extends Resource
{
    protected static ?string $model = AuditEntry::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Audit Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Entity')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditEntries::route('/'),
        ];
    }
}
