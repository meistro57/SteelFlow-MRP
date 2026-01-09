<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\JobStatus;
use App\Filament\Resources\FabJobResource\Pages;
use App\Models\FabJob;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FabJobResource extends Resource
{
    protected static ?string $model = FabJob::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Production';

    protected static ?string $navigationLabel = 'Jobs';

    protected static ?string $modelLabel = 'Job';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Job Information')
                ->schema([
                    TextInput::make('job_number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(30)
                        ->placeholder('J2024-001'),

                    TextInput::make('customer_name')
                        ->required()
                        ->maxLength(255),

                    Select::make('status')
                        ->options(JobStatus::class)
                        ->required()
                        ->default(JobStatus::Estimating),

                    DatePicker::make('due_date')
                        ->native(false),
                ])->columns(2),

            Section::make('Description')
                ->schema([
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parts_count')
                    ->label('Parts')
                    ->counts('parts')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn (FabJob $record) => $record->due_date && $record->due_date->isPast() ? 'danger' : null),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(JobStatus::class)
                    ->multiple(),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn ($query) => $query->where('due_date', '<', now())->where('status', '!=', JobStatus::Complete))
                    ->label('Overdue Jobs'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
                Tables\Actions\ExportBulkAction::make()
                    ->exporter(\App\Filament\Exports\FabJobExporter::class),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\FabJobExporter::class),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            FabJobResource\RelationManagers\PartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFabJobs::route('/'),
            'create' => Pages\CreateFabJob::route('/create'),
            'view' => Pages\ViewFabJob::route('/{record}'),
            'edit' => Pages\EditFabJob::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', JobStatus::InProgress)->count() ?: null;
    }
}
