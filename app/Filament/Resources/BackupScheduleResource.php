<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BackupScheduleResource\Pages;
use App\Filament\Resources\BackupScheduleResource\RelationManagers;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Backup\Models\BackupSchedule;

class BackupScheduleResource extends Resource
{
    protected static ?string $model = BackupSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Backup Schedules';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Schedule Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Daily Database Backup'),
                    Select::make('type')
                        ->required()
                        ->options([
                            'database' => 'Database',
                            'files' => 'Files',
                            'full' => 'Full System',
                        ])
                        ->default('database'),
                    Select::make('frequency')
                        ->required()
                        ->options([
                            'hourly' => 'Hourly',
                            'daily' => 'Daily',
                            'weekly' => 'Weekly',
                            'monthly' => 'Monthly',
                        ])
                        ->default('daily')
                        ->reactive(),
                    TimePicker::make('time')
                        ->label('Run Time')
                        ->seconds(false)
                        ->default('02:00')
                        ->visible(fn ($get) => in_array($get('frequency'), ['daily', 'weekly', 'monthly'])),
                ])->columns(2),

            Section::make('Settings')
                ->schema([
                    Toggle::make('enabled')
                        ->label('Enabled')
                        ->default(true)
                        ->required(),
                    Toggle::make('cloud_sync')
                        ->label('Sync to Cloud Storage')
                        ->default(false),
                    TextInput::make('retention_days')
                        ->label('Retention (Days)')
                        ->numeric()
                        ->default(30)
                        ->minValue(1)
                        ->maxValue(365)
                        ->required()
                        ->helperText('Number of days to keep backups before automatic cleanup'),
                ])->columns(3),

            Section::make('Advanced Options')
                ->schema([
                    KeyValue::make('options')
                        ->label('Additional Options')
                        ->keyLabel('Option Name')
                        ->valueLabel('Value')
                        ->addable()
                        ->deletable()
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(),
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
                Tables\Columns\TextColumn::make('frequency')
                    ->badge()
                    ->colors([
                        'gray' => 'hourly',
                        'info' => 'daily',
                        'success' => 'weekly',
                        'warning' => 'monthly',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->label('Run Time')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('enabled')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('cloud_sync')
                    ->label('Cloud Sync')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('retention_days')
                    ->label('Retention')
                    ->formatStateUsing(fn ($state) => $state.' days')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_run_at')
                    ->label('Last Run')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('next_run_at')
                    ->label('Next Run')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not scheduled'),
                Tables\Columns\TextColumn::make('backups_count')
                    ->label('Backups')
                    ->counts('backups')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Enabled Status'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'database' => 'Database',
                        'files' => 'Files',
                        'full' => 'Full System',
                    ]),
                Tables\Filters\SelectFilter::make('frequency')
                    ->options([
                        'hourly' => 'Hourly',
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                    ]),
            ])
            ->actions([
                Actions\Action::make('toggle')
                    ->label(fn (BackupSchedule $record) => $record->enabled ? 'Disable' : 'Enable')
                    ->icon(fn (BackupSchedule $record) => $record->enabled ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (BackupSchedule $record) => $record->enabled ? 'warning' : 'success')
                    ->action(function (BackupSchedule $record) {
                        $record->update(['enabled' => ! $record->enabled]);
                    }),
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['enabled' => true])),
                    Actions\BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['enabled' => false])),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BackupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBackupSchedules::route('/'),
            'create' => Pages\CreateBackupSchedule::route('/create'),
            'view' => Pages\ViewBackupSchedule::route('/{record}'),
            'edit' => Pages\EditBackupSchedule::route('/{record}/edit'),
        ];
    }
}
