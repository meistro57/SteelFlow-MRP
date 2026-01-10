<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BackupResource\Pages;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
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
use Modules\Backup\Models\Backup;
use Modules\Backup\Services\BackupService;
use Modules\Backup\Services\RestoreService;

class BackupResource extends Resource
{
    protected static ?string $model = Backup::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Backups';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Backup Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->default(fn () => 'backup_'.now()->format('Y-m-d_His')),
                    Select::make('type')
                        ->required()
                        ->options([
                            'database' => 'Database',
                            'files' => 'Files',
                            'full' => 'Full System',
                        ])
                        ->default('database'),
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
                    Textarea::make('notes')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Backup Details')
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
                        ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024 / 1024, 2).' MB' : 'N/A'),
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
                ])->columns(2)->hidden(fn ($livewire) => $livewire instanceof Pages\CreateBackup),
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
                        'primary' => 'database',
                        'success' => 'files',
                        'warning' => 'full',
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
                Tables\Columns\TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024 / 1024, 2).' MB' : 'N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('completed_at')
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
                        'database' => 'Database',
                        'files' => 'Files',
                        'full' => 'Full System',
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
                    ->action(function (Backup $record) {
                        if (! $record->path || ! Storage::exists($record->path)) {
                            Notification::make()
                                ->danger()
                                ->title('Backup file not found')
                                ->send();

                            return;
                        }

                        return Storage::download($record->path, basename($record->path));
                    })
                    ->visible(fn (Backup $record) => $record->isCompleted()),
                Actions\Action::make('restore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Backup')
                    ->modalDescription('Are you sure you want to restore this backup? This will overwrite current data.')
                    ->action(function (Backup $record) {
                        try {
                            $restoreService = app(RestoreService::class);
                            $restoreService->restore($record);

                            Notification::make()
                                ->success()
                                ->title('Backup restored successfully')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Restore failed')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn (Backup $record) => $record->isCompleted()),
                Actions\ViewAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Actions\Action::make('create_backup')
                    ->label('Create Backup Now')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Select::make('type')
                            ->label('Backup Type')
                            ->required()
                            ->options([
                                'database' => 'Database',
                                'files' => 'Files',
                                'full' => 'Full System',
                            ])
                            ->default('database'),
                        TextInput::make('name')
                            ->label('Backup Name')
                            ->default(fn () => 'backup_'.now()->format('Y-m-d_His')),
                        Textarea::make('notes')
                            ->label('Notes'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $backupService = app(BackupService::class);
                            $backup = $backupService->create($data['type'], [
                                'name' => $data['name'] ?? null,
                                'notes' => $data['notes'] ?? null,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Backup created successfully')
                                ->body("Backup '{$backup->name}' has been created.")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Backup creation failed')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBackups::route('/'),
            'create' => Pages\CreateBackup::route('/create'),
            'view' => Pages\ViewBackup::route('/{record}'),
            'edit' => Pages\EditBackup::route('/{record}/edit'),
        ];
    }
}
