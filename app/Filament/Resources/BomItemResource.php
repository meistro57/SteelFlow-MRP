<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BomItemResource\Pages;
use App\Models\BomItem;
use App\Models\RawMaterial;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class BomItemResource extends Resource
{
    protected static ?string $model = BomItem::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static UnitEnum|string|null $navigationGroup = 'Production';

    protected static ?string $navigationLabel = 'BOM Items';

    protected static ?string $modelLabel = 'BOM Item';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('BOM Item Details')
                ->schema([
                    Select::make('fab_part_id')
                        ->label('Part')
                        ->relationship('fabPart', 'mark_number', fn ($query) => $query->with('fabJob'))
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->fabJob->job_number} - {$record->mark_number}")
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('raw_material_id')
                        ->label('Material')
                        ->options(fn () => RawMaterial::all()->pluck('display_name', 'id'))
                        ->searchable()
                        ->required(),

                    TextInput::make('quantity_required')
                        ->label('Quantity Required')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->minValue(1),

                    TextInput::make('cut_length')
                        ->label('Cut Length')
                        ->numeric()
                        ->suffix('ft')
                        ->step(0.0001),
                ])->columns(2),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fabPart.fabJob.job_number')
                    ->label('Job')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('fabPart.mark_number')
                    ->label('Part Mark')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('rawMaterial.display_name')
                    ->label('Material')
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity_required')
                    ->label('Qty Req')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cut_length')
                    ->label('Cut Length')
                    ->numeric(decimalPlaces: 4)
                    ->suffix(' ft')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rawMaterial.quantity_on_hand')
                    ->label('In Stock')
                    ->sortable()
                    ->color(fn ($record) => $record->rawMaterial?->isLowStock() ? 'danger' : null),

                Tables\Columns\TextColumn::make('notes')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fab_part_id')
                    ->label('Part')
                    ->relationship('fabPart', 'mark_number')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('raw_material_id')
                    ->label('Material')
                    ->relationship('rawMaterial', 'size_description')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBomItems::route('/'),
            'create' => Pages\CreateBomItem::route('/create'),
            'edit' => Pages\EditBomItem::route('/{record}/edit'),
        ];
    }
}
