<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AssemblyResource\Pages;
use App\Models\Assembly;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class AssemblyResource extends Resource
{
    protected static ?string $model = Assembly::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-queue-list';

    protected static UnitEnum|string|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Assembly Details')
                ->schema([
                    Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('mark')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('description')
                        ->maxLength(255),
                    TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->default(1),
                    Select::make('drawing_id')
                        ->relationship('drawing', 'number')
                        ->searchable()
                        ->preload(),
                ])->columns(2),

            Section::make('Main Member Information')
                ->schema([
                    TextInput::make('main_member_type')
                        ->maxLength(50),
                    TextInput::make('main_member_size')
                        ->maxLength(100),
                    TextInput::make('main_member_grade')
                        ->maxLength(30),
                    TextInput::make('main_member_length')
                        ->numeric()
                        ->suffix('ft'),
                ])->columns(2),

            Section::make('Weights')
                ->schema([
                    TextInput::make('weight_each_lbs')
                        ->label('Weight Each (lbs)')
                        ->numeric()
                        ->suffix('lbs'),
                    TextInput::make('weight_each_kg')
                        ->label('Weight Each (kg)')
                        ->numeric()
                        ->suffix('kg'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mark')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.job_number')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_weight_lbs')
                    ->label('Total Weight (lbs)')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' lbs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('drawing.number')
                    ->label('Drawing')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('mark');
    }

    public static function getRelations(): array
    {
        return [
            AssemblyResource\RelationManagers\PartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssemblies::route('/'),
            'create' => Pages\CreateAssembly::route('/create'),
            'edit' => Pages\EditAssembly::route('/{record}/edit'),
        ];
    }
}
