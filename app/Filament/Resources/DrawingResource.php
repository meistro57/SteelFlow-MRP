<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DrawingResource\Pages;
use App\Models\Drawing;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DrawingResource extends Resource
{
    protected static ?string $model = Drawing::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Drawing Details')
                ->schema([
                    Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('number')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('revision')
                        ->maxLength(10)
                        ->default('0'),
                    TextInput::make('title')
                        ->maxLength(255),
                    DatePicker::make('revised_at')
                        ->native(false),
                ])->columns(2),

            Section::make('File')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Drawing File')
                        ->directory('drawings')
                        ->visibility('public')
                        ->preserveFilenames(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revision')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('project.job_number')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revised_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn (Drawing $record) => !empty($record->file_path)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('number');
    }

    public static function getRelations(): array
    {
        return [
            DrawingResource\RelationManagers\AssembliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrawings::route('/'),
            'create' => Pages\CreateDrawing::route('/create'),
            'edit' => Pages\EditDrawing::route('/{record}/edit'),
        ];
    }
}
