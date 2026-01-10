<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionBatchResource\Pages;
use App\Models\ProductionBatch;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductionBatchResource extends Resource
{
    protected static ?string $model = ProductionBatch::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|\UnitEnum|null $navigationGroup = 'Production';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Batch Details')
                ->schema([
                    TextInput::make('batch_number')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('project_id')
                        ->relationship('project', 'name'),
                    Select::make('status')
                        ->options([
                            'created' => 'Created',
                            'released' => 'Released',
                            'in_progress' => 'In Progress',
                            'complete' => 'Complete',
                        ])
                        ->default('created')
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('batch_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('project.job_number')->label('Project')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'created' => 'Created',
                    'released' => 'Released',
                    'in_progress' => 'In Progress',
                    'complete' => 'Complete',
                ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionBatches::route('/'),
            'create' => Pages\CreateProductionBatch::route('/create'),
            'edit' => Pages\EditProductionBatch::route('/{record}/edit'),
        ];
    }
}
