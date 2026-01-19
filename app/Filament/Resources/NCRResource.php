<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NCRResource\Pages;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\NCR\Models\NCR;
use Modules\NCR\Services\NCRService;
use Modules\PdfCenter\Services\PdfService;
use UnitEnum;

class NCRResource extends Resource
{
    protected static ?string $model = NCR::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static UnitEnum|string|null $navigationGroup = 'Production';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'NCRs (Quality)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')
                ->required()
                ->unique(ignoreRecord: true)
                ->default(fn () => 'NCR-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(2)))),

            Select::make('part_instance_id')
                ->relationship('partInstance', 'id')
                ->searchable()
                ->preload()
                ->label('Part Instance'),

            Select::make('stock_item_id')
                ->relationship('stockItem', 'heat_number')
                ->searchable()
                ->preload()
                ->label('Source Material (Stock)'),

            Select::make('status')
                ->options([
                    'open' => 'Open',
                    'under_review' => 'Under Review',
                    'dispositioned' => 'Dispositioned',
                    'closed' => 'Closed',
                ])
                ->disabled()
                ->dehydrated(false),

            Textarea::make('failure_reason')
                ->required()
                ->columnSpanFull(),

            Select::make('disposition')
                ->options([
                    'SCRAP' => 'Scrap (Discard & Remake)',
                    'REWORK' => 'Rework (Repair)',
                    'USE_AS_IS' => 'Use As Is (Override)',
                ])
                ->visible(fn ($record) => $record && $record->status->getName() !== 'open'),

            Textarea::make('remediation_notes')
                ->columnSpanFull()
                ->visible(fn ($record) => $record && $record->status->getName() !== 'open'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('partInstance.part.part_mark')
                    ->label('Part Mark')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status->label())
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'under_review',
                        'success' => 'dispositioned',
                        'gray' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('disposition')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Reported By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'under_review' => 'Under Review',
                        'dispositioned' => 'Dispositioned',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Actions\Action::make('download_report')
                    ->label('Report PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (NCR $record) {
                        $pdfService = app(PdfService::class);

                        return $pdfService->generate('pdfcenter::templates.ncr_report', ['ncr' => $record])
                            ->download($record->number.'_Report.pdf');
                    }),
                Actions\Action::make('disposition')
                    ->icon('heroicon-o-check-badge')
                    ->color('warning')
                    ->form([
                        Select::make('disposition')
                            ->options([
                                'SCRAP' => 'Scrap (Discard & Remake)',
                                'REWORK' => 'Rework (Repair)',
                                'USE_AS_IS' => 'Use As Is (Override)',
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label('Remediation Notes')
                            ->required(),
                        TextInput::make('rework_operation')
                            ->label('Rework Operation')
                            ->placeholder('e.g., Grind smooth, Re-weld')
                            ->visible(fn ($get) => $get('disposition') === 'REWORK'),
                    ])
                    ->action(function (NCR $record, array $data) {
                        $service = app(NCRService::class);
                        $service->disposition($record, $data['disposition'], $data);

                        Notification::make()
                            ->success()
                            ->title('NCR Dispositioned')
                            ->body("Action '{$data['disposition']}' has been executed.")
                            ->send();
                    })
                    ->visible(fn (NCR $record) => in_array($record->status->getName(), ['open', 'under_review'])),

                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNCRs::route('/'),
            'create' => Pages\CreateNCR::route('/create'),
            'edit' => Pages\EditNCR::route('/{record}/edit'),
        ];
    }
}
