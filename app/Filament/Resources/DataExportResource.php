<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DataExportResource\Pages;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Modules\Backup\Models\DataExport;
use Modules\Backup\Services\ExportService;

class DataExportResource extends Resource
{
    protected static ?string $model = DataExport::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Data Exports';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Export Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Projects Export')
                        ->helperText('Descriptive name for this export'),
                    Select::make('type')
                        ->required()
                        ->options([
                            'projects' => 'Projects',
                            'customers' => 'Customers',
                            'inventory' => 'Inventory',
                            'purchase_orders' => 'Purchase Orders',
                            'assemblies' => 'Assemblies',
                            'parts' => 'Parts',
                            'stock_items' => 'Stock Items',
                            'production_items' => 'Production Items',
                            'loads' => 'Loads',
                            'full' => 'Full Database Export',
                        ])
                        ->default('projects')
                        ->helperText('Select the type of data to export'),
                    Select::make('format')
                        ->required()
                        ->options([
                            'csv' => 'CSV',
                            'xlsx' => 'Excel (XLSX)',
                            'json' => 'JSON',
                            'xml' => 'XML',
                        ])
                        ->default('csv')
                        ->helperText('Export file format'),
                    Select::make('status')
                        ->required()
                        ->options([
                            'pending' => 'Pending',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'failed' => 'Failed',
                        ])
                        ->disabled()
                        ->dehydrated(false),
                ])->columns(2),

            Section::make('Filters')
                ->schema([
                    KeyValue::make('filters')
                        ->label('Export Filters')
                        ->keyLabel('Field')
                        ->valueLabel('Value')
                        ->addable()
                        ->deletable()
                        ->columnSpanFull()
                        ->helperText('Add filters to limit the exported data (e.g., status=active, created_after=2024-01-01)'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Export Details')
                ->schema([
                    TextInput::make('path')
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('cloud_path')
                        ->maxLength(255)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('size')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2).' KB' : 'N/A'),
                    TextInput::make('row_count')
                        ->label('Records Exported')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false),
                    DateTimePicker::make('started_at')
                        ->disabled()
                        ->dehydrated(false),
                    DateTimePicker::make('completed_at')
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('error_message')
                        ->columnSpanFull()
                        ->disabled()
                        ->dehydrated(false),
                ])->columns(2)->hidden(fn ($livewire) => $livewire instanceof Pages\CreateDataExport),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'projects',
                        'success' => 'customers',
                        'info' => 'inventory',
                        'warning' => 'full',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('format')
                    ->badge()
                    ->colors([
                        'gray' => 'csv',
                        'success' => 'xlsx',
                        'info' => 'json',
                        'warning' => 'xml',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('row_count')
                    ->label('Records')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2).' KB' : 'N/A')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'projects' => 'Projects',
                        'customers' => 'Customers',
                        'inventory' => 'Inventory',
                        'purchase_orders' => 'Purchase Orders',
                        'assemblies' => 'Assemblies',
                        'parts' => 'Parts',
                        'full' => 'Full Database',
                    ]),
                Tables\Filters\SelectFilter::make('format')
                    ->options([
                        'csv' => 'CSV',
                        'xlsx' => 'Excel',
                        'json' => 'JSON',
                        'xml' => 'XML',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (DataExport $record) {
                        if (! $record->path || ! Storage::exists($record->path)) {
                            Notification::make()
                                ->danger()
                                ->title('Export file not found')
                                ->send();

                            return;
                        }

                        return Storage::download($record->path, $record->download_filename);
                    })
                    ->visible(fn (DataExport $record) => $record->isCompleted()),
                Actions\ViewAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Actions\Action::make('create_export')
                    ->label('Create Export Now')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Select::make('type')
                            ->label('Data Type')
                            ->required()
                            ->options([
                                'projects' => 'Projects',
                                'customers' => 'Customers',
                                'inventory' => 'Inventory',
                                'purchase_orders' => 'Purchase Orders',
                                'assemblies' => 'Assemblies',
                                'parts' => 'Parts',
                                'stock_items' => 'Stock Items',
                                'production_items' => 'Production Items',
                                'loads' => 'Loads',
                                'full' => 'Full Database Export',
                            ])
                            ->default('projects'),
                        Select::make('format')
                            ->label('Format')
                            ->required()
                            ->options([
                                'csv' => 'CSV',
                                'xlsx' => 'Excel (XLSX)',
                                'json' => 'JSON',
                                'xml' => 'XML',
                            ])
                            ->default('csv'),
                        TextInput::make('name')
                            ->label('Export Name')
                            ->default(fn () => 'export_'.now()->format('Y-m-d_His')),
                        KeyValue::make('filters')
                            ->label('Filters (Optional)')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $exportService = app(ExportService::class);
                            $export = $exportService->export(
                                $data['type'],
                                $data['format'],
                                [
                                    'name' => $data['name'] ?? null,
                                    'filters' => $data['filters'] ?? [],
                                ],
                            );

                            Notification::make()
                                ->success()
                                ->title('Export created successfully')
                                ->body("Export '{$export->name}' has been created and is ready for download.")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Export creation failed')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataExports::route('/'),
            'create' => Pages\CreateDataExport::route('/create'),
            'view' => Pages\ViewDataExport::route('/{record}'),
            'edit' => Pages\EditDataExport::route('/{record}/edit'),
        ];
    }
}
