<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Inventory\Models\Vendor;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Procurement';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Vendor Information')
                ->schema([
                    TextInput::make('code')
                        ->maxLength(20)
                        ->placeholder('VEN001'),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('contact_name')
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('payment_terms')
                        ->maxLength(100)
                        ->placeholder('Net 30, Due on Receipt, etc.'),
                ])->columns(2),

            Section::make('Address')
                ->schema([
                    TextInput::make('address_1')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->maxLength(100),
                    TextInput::make('state')
                        ->maxLength(100),
                    TextInput::make('zip')
                        ->maxLength(20),
                ])->columns(2),

            Section::make('Additional Info')
                ->schema([
                    Textarea::make('notes')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
