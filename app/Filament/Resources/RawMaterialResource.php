<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\MaterialType;
use App\Filament\Resources\RawMaterialResource\Pages;
use App\Models\RawMaterial;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RawMaterialResource extends Resource
{
    protected static ?string $model = RawMaterial::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Materials';

    protected static ?string $modelLabel = 'Material';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Material Information')
                ->schema([
                    Select::make('material_type')
                        ->options(MaterialType::class)
                        ->required()
                        ->native(false),

                    TextInput::make('size_description')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('W10x26, L4x4x1/4, etc.'),

                    TextInput::make('grade')
                        ->required()
                        ->default('A36')
                        ->maxLength(30)
                        ->datalist([
                            'A36',
                            'A572-50',
                            'A992',
                            'A500B',
                            'A500C',
                            'A53B',
                        ]),

                    TextInput::make('length_stock')
                        ->label('Stock Length')
                        ->numeric()
                        ->suffix('ft')
                        ->step(0.0001)
                        ->placeholder('20, 40, etc.'),
                ])->columns(2),

            Section::make('Inventory')
                ->schema([
                    TextInput::make('quantity_on_hand')
                        ->label('Quantity On Hand')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('location')
                        ->maxLength(100)
                        ->placeholder('Bay A-1, Rack 3, etc.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('material_type')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('size_description')
                    ->label('Size')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('length_stock')
                    ->label('Length')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ft')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('Qty')
                    ->sortable()
                    ->color(fn (RawMaterial $record) => $record->isLowStock() ? 'danger' : null)
                    ->weight(fn (RawMaterial $record) => $record->isLowStock() ? 'bold' : null),

                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('low_stock')
                    ->label('Low Stock')
                    ->boolean()
                    ->getStateUsing(fn (RawMaterial $record) => $record->isLowStock())
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('material_type')
                    ->options(MaterialType::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('grade')
                    ->options([
                        'A36' => 'A36',
                        'A572-50' => 'A572-50',
                        'A992' => 'A992',
                        'A500B' => 'A500B',
                        'A500C' => 'A500C',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('low_stock')
                    ->query(fn (Builder $query) => $query->where('quantity_on_hand', '<', 5))
                    ->label('Low Stock Only'),

                Tables\Filters\Filter::make('out_of_stock')
                    ->query(fn (Builder $query) => $query->where('quantity_on_hand', '=', 0))
                    ->label('Out of Stock'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('adjustQuantity')
                    ->label('Adjust Qty')
                    ->icon('heroicon-o-plus-minus')
                    ->form([
                        TextInput::make('adjustment')
                            ->label('Adjustment (+/-)')
                            ->numeric()
                            ->required()
                            ->helperText('Positive to add, negative to subtract'),
                    ])
                    ->action(function (RawMaterial $record, array $data): void {
                        $newQty = max(0, $record->quantity_on_hand + (int) $data['adjustment']);
                        $record->update(['quantity_on_hand' => $newQty]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('size_description');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRawMaterials::route('/'),
            'create' => Pages\CreateRawMaterial::route('/create'),
            'edit' => Pages\EditRawMaterial::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $lowStockCount = static::getModel()::where('quantity_on_hand', '<', 5)->count();

        return $lowStockCount > 0 ? (string) $lowStockCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
