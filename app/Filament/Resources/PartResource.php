<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PartResource\Pages;
use App\Models\Part;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string|\UnitEnum|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Part Details')
                ->schema([
                    Select::make('project_id')
                        ->relationship('project', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('assembly_id')
                        ->relationship('assembly', 'mark')
                        ->searchable()
                        ->preload(),
                    TextInput::make('part_mark')
                        ->required()
                        ->maxLength(50),
                    TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->default(1),
                ])->columns(2),

            Section::make('Material & Geometry')
                ->schema([
                    TextInput::make('type')
                        ->label('Material Type')
                        ->placeholder('Beam, Plate, Angle, etc.'),
                    TextInput::make('size_imperial')
                        ->label('Size (Imperial)')
                        ->placeholder('W10x26'),
                    TextInput::make('grade')
                        ->placeholder('A992, A36, etc.'),
                    TextInput::make('length')
                        ->numeric()
                        ->suffix('in'),
                    Toggle::make('is_main_member')
                        ->label('Main Member'),
                ])->columns(2),

            Section::make('Weights')
                ->schema([
                    TextInput::make('weight_each_lbs')
                        ->label('Weight Each (lbs)')
                        ->numeric()
                        ->suffix('lbs'),
                    TextInput::make('total_weight_lbs')
                        ->label('Total Weight (lbs)')
                        ->numeric()
                        ->prefix('Auto-calculated'),
                ])->columns(2),

            Section::make('Additional Info')
                ->schema([
                    TextInput::make('finish'),
                    TextInput::make('remarks'),
                    Toggle::make('nc_data_available')
                        ->label('NC Data Available'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('part_mark')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assembly.mark')
                    ->label('Assy Mark')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.job_number')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('size_imperial')
                    ->label('Size')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_weight_lbs')
                    ->label('Weight (lbs)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\IconColumn::make('nc_data_available')
                    ->label('NC')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
                Tables\Filters\TernaryFilter::make('is_main_member'),
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
            ->defaultSort('part_mark');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParts::route('/'),
            'create' => Pages\CreatePart::route('/create'),
            'edit' => Pages\EditPart::route('/{record}/edit'),
        ];
    }
}
