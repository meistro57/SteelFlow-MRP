<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LoadResource\Pages;
use App\Models\Load;
use BackedEnum;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class LoadResource extends Resource
{
    protected static ?string $model = Load::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static UnitEnum|string|null $navigationGroup = 'Shipping';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Load Information')
                ->schema([
                    TextInput::make('load_number')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('project_id')
                        ->relationship('project', 'name')
                        ->required(),
                    Select::make('status')
                        ->options([
                            'open' => 'Open',
                            'ready' => 'Ready to Ship',
                            'in_transit' => 'In Transit (Shipped)',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('open')
                        ->required(),
                    TextInput::make('bol_number')->label('BOL #'),
                ])->columns(2),

            Section::make('Carrier & Logistics')
                ->schema([
                    TextInput::make('carrier'),
                    TextInput::make('driver_name'),
                    TextInput::make('truck_number'),
                    TextInput::make('trailer_number'),
                    DatePicker::make('ship_date')->native(false),
                    TextInput::make('destination'),
                ])->columns(2),

            Section::make('Totals')
                ->schema([
                    TextInput::make('total_weight_lbs')->numeric()->suffix('lbs')->readOnly(),
                    TextInput::make('total_pieces')->numeric()->readOnly(),
                ])->columns(2),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('load_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('project.job_number')->label('Project')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('ship_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('total_weight_lbs')->label('Weight (lbs)')->suffix(' lbs'),
                Tables\Columns\TextColumn::make('total_pieces')->label('Pieces'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'open' => 'Open',
                    'ready' => 'Ready to Ship',
                    'in_transit' => 'In Transit (Shipped)',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('project')->relationship('project', 'name'),
            ])
            ->actions([
                Action::make('ship')
                    ->label('Mark Shipped')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Load $record) => $record->status === 'ready')
                    ->action(fn (Load $record) => $record->update(['status' => 'in_transit', 'shipped_at' => now()])),

                Action::make('deliver')
                    ->label('Confirm Delivery')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->visible(fn (Load $record) => $record->status === 'in_transit')
                    ->action(fn (Load $record) => $record->update(['status' => 'delivered', 'delivered_at' => now()])),

                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            LoadResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoads::route('/'),
            'create' => Pages\CreateLoad::route('/create'),
            'view' => Pages\ViewLoad::route('/{record}'),
            'edit' => Pages\EditLoad::route('/{record}/edit'),
        ];
    }
}
