<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NestingResource\Pages;
use App\Models\Nesting;
use App\Services\Nesting\NestingService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NestingResource extends Resource
{
    protected static ?string $model = Nesting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string|\UnitEnum|null $navigationGroup = 'Production';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nesting Details')
                ->schema([
                    TextInput::make('nesting_number')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('project_id')
                        ->relationship('project', 'name')
                        ->required(),
                    Select::make('type')
                        ->options([
                            'linear' => 'Linear (Bars)',
                            'plate' => 'Plate',
                        ])
                        ->required(),
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'approved' => 'Approved',
                            'verified' => 'Verified',
                            'confirmed' => 'Confirmed',
                        ])
                        ->default('draft')
                        ->required(),
                ])->columns(2),

            Section::make('Material Requirements')
                ->schema([
                    TextInput::make('material_type'),
                    TextInput::make('grade'),
                    TextInput::make('kerf_allowance')
                        ->numeric()
                        ->default(0.125)
                        ->suffix('in'),
                ])->columns(3),

            Section::make('Statistics')
                ->schema([
                    TextInput::make('total_bars')->numeric()->readOnly(),
                    TextInput::make('total_parts')->numeric()->readOnly(),
                    TextInput::make('efficiency_percent')
                        ->numeric()
                        ->suffix('%')
                        ->readOnly(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nesting_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.job_number')
                    ->label('Project')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'info',
                        'verified' => 'warning',
                        'confirmed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('efficiency_percent')
                    ->label('Efficiency')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_bars')
                    ->label('Bars'),
                Tables\Columns\TextColumn::make('total_parts')
                    ->label('Parts'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'verified' => 'Verified',
                        'confirmed' => 'Confirmed',
                    ]),
                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name'),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Nesting $record) => $record->status === 'draft')
                    ->action(fn (Nesting $record) => app(NestingService::class)->approve($record)),

                Actions\Action::make('confirm')
                    ->label('Confirm Cut')
                    ->icon('heroicon-o-scissors')
                    ->color('success')
                    ->visible(fn (Nesting $record) => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->action(fn (Nesting $record) => app(NestingService::class)->confirm($record)),

                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            NestingResource\RelationManagers\BarsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNestings::route('/'),
            'create' => Pages\CreateNesting::route('/create'),
            'view' => Pages\ViewNesting::route('/{record}'),
            'edit' => Pages\EditNesting::route('/{record}/edit'),
        ];
    }
}
