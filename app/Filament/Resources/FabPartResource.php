<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PartStatus;
use App\Filament\Resources\FabPartResource\Pages;
use App\Models\FabPart;
use App\Models\RawMaterial;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FabPartResource extends Resource
{
    protected static ?string $model = FabPart::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Production';

    protected static ?string $navigationLabel = 'Parts';

    protected static ?string $modelLabel = 'Part';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Part Information')
                ->schema([
                    Select::make('fab_job_id')
                        ->label('Job')
                        ->relationship('fabJob', 'job_number')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('job_number')
                                ->required()
                                ->unique(),
                            TextInput::make('customer_name')
                                ->required(),
                        ]),

                    TextInput::make('mark_number')
                        ->required()
                        ->maxLength(30)
                        ->placeholder('B1, C2, etc.'),

                    TextInput::make('description')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Specifications')
                ->schema([
                    TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->minValue(1),

                    TextInput::make('material_grade')
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

                    TextInput::make('weight_each')
                        ->numeric()
                        ->suffix('lbs')
                        ->step(0.01),

                    Select::make('status')
                        ->options(PartStatus::class)
                        ->default(PartStatus::Pending)
                        ->required(),
                ])->columns(2),

            Section::make('Bill of Materials')
                ->schema([
                    Repeater::make('bomItems')
                        ->relationship()
                        ->schema([
                            Select::make('raw_material_id')
                                ->label('Material')
                                ->options(fn () => RawMaterial::all()->pluck('display_name', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('quantity_required')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),

                            TextInput::make('cut_length')
                                ->label('Cut Length')
                                ->numeric()
                                ->step(0.0001)
                                ->suffix('ft'),

                            TextInput::make('notes')
                                ->maxLength(255)
                                ->columnSpan(2),
                        ])
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Add Material')
                        ->reorderable(false)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['raw_material_id']
                                ? RawMaterial::find($state['raw_material_id'])?->display_name
                                : null,
                        ),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fabJob.job_number')
                    ->label('Job')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('mark_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),

                Tables\Columns\TextColumn::make('material_grade')
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight_each')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' lbs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bomItems_count')
                    ->label('BOM Items')
                    ->counts('bomItems')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('fab_job_id')
                    ->label('Job')
                    ->relationship('fabJob', 'job_number')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(PartStatus::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('material_grade')
                    ->options([
                        'A36' => 'A36',
                        'A572-50' => 'A572-50',
                        'A992' => 'A992',
                        'A500B' => 'A500B',
                        'A500C' => 'A500C',
                    ])
                    ->multiple(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('status')
                            ->options(PartStatus::class)
                            ->required(),
                    ])
                    ->action(fn (FabPart $record, array $data) => $record->update(['status' => $data['status']])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\BulkAction::make('bulkUpdateStatus')
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
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(\App\Filament\Exports\FabPartExporter::class),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\FabPartExporter::class),
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
            'index' => Pages\ListFabParts::route('/'),
            'create' => Pages\CreateFabPart::route('/create'),
            'edit' => Pages\EditFabPart::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', PartStatus::Pending)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
