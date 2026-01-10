<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Project Information')
                ->schema([
                    TextInput::make('job_number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(30),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('status')
                        ->options([
                            'bid' => 'Bid',
                            'active' => 'Active',
                            'production' => 'Production',
                            'shipping' => 'Shipping',
                            'complete' => 'Complete',
                            'archived' => 'Archived',
                        ])
                        ->default('bid')
                        ->required(),
                ])->columns(2),

            Section::make('Job Details')
                ->schema([
                    Select::make('job_type')
                        ->options([
                            'new_construction' => 'New Construction',
                            'renovation' => 'Renovation',
                            'repair' => 'Repair',
                            'miscellaneous' => 'Miscellaneous',
                        ]),
                    TextInput::make('po_number')
                        ->label('PO Number')
                        ->maxLength(50),
                    DatePicker::make('ship_date')
                        ->native(false),
                ])->columns(3),

            Section::make('Weights')
                ->schema([
                    TextInput::make('contract_weight_lbs')
                        ->label('Contract Weight (lbs)')
                        ->numeric()
                        ->suffix('lbs'),
                    TextInput::make('contract_weight_kg')
                        ->label('Contract Weight (kg)')
                        ->numeric()
                        ->suffix('kg'),
                ])->columns(2),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ship_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assemblies_count')
                    ->label('Assy')
                    ->counts('assemblies')
                    ->sortable(),
                Tables\Columns\TextColumn::make('parts_count')
                    ->label('Parts')
                    ->counts('parts')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'bid' => 'Bid',
                        'active' => 'Active',
                        'production' => 'Production',
                        'shipping' => 'Shipping',
                        'complete' => 'Complete',
                        'archived' => 'Archived',
                    ]),
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('job_number');
    }

    public static function getRelations(): array
    {
        return [
            ProjectResource\RelationManagers\AssembliesRelationManager::class,
            ProjectResource\RelationManagers\DrawingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
