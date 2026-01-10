<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LoadResource\Pages;
use App\Models\Load;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class LoadResource extends Resource
{
    protected static ?string $model = Load::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Shipping';

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
                            'not_started' => 'Not Started',
                            'in_progress' => 'In Progress',
                            'ready' => 'Ready to Ship',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                        ])
                        ->default('not_started')
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
                    'not_started' => 'Not Started',
                    'in_progress' => 'In Progress',
                    'ready' => 'Ready to Ship',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                ]),
                Tables\Filters\SelectFilter::make('project')->relationship('project', 'name'),
            ])
            ->actions([
                Action::make('ship')
                    ->label('Mark Shipped')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Load $record) => $record->status === 'ready')
                    ->action(fn (Load $record) => $record->update(['status' => 'shipped', 'shipped_at' => now()])),

                Action::make('deliver')
                    ->label('Confirm Delivery')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->visible(fn (Load $record) => $record->status === 'shipped')
                    ->action(fn (Load $record) => $record->update(['status' => 'delivered', 'delivered_at' => now()])),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
