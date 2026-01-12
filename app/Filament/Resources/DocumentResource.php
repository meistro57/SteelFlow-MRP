<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Documents\Models\Document;
use UnitEnum;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('file_path')
                    ->required()
                    ->directory('documents')
                    ->preserveFilenames()
                    ->maxSize(51200), // 50MB
                Select::make('file_type')
                    ->options([
                        'pdf' => 'PDF',
                        'dwg' => 'DWG',
                        'dxf' => 'DXF',
                        'kiss' => 'KISS',
                        'smlx' => 'SMLX',
                        'other' => 'Other',
                    ]),
                TextInput::make('version')
                    ->numeric()
                    ->default(1)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($record) => $record->formatted_size)
                    ->sortable(),
                Tables\Columns\TextColumn::make('documentable_type')
                    ->label('Attached To Type')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('file_type')
                    ->options([
                        'pdf' => 'PDF',
                        'dwg' => 'DWG',
                        'dxf' => 'DXF',
                        'kiss' => 'KISS',
                        'smlx' => 'SMLX',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Document $record) => \Illuminate\Support\Facades\Storage::download($record->file_path, $record->name)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
