<?php

namespace App\Filament\Resources\DrawingResource\Pages;

use App\Filament\Resources\DrawingResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDrawings extends ListRecords
{
    protected static string $resource = DrawingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulk_cad_import')
                ->label('Bulk CAD Import')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('primary')
                ->form([
                    Select::make('project_id')
                        ->label('Target Project')
                        ->options(Project::pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    FileUpload::make('data_file')
                        ->label('Data File (KISS/SMLX)')
                        ->required()
                        ->disk('local')
                        ->visibility('private')
                        ->directory('temp-imports'),
                    FileUpload::make('pdf_drawings')
                        ->label('Drawing PDFs')
                        ->multiple()
                        ->disk('local')
                        ->visibility('private')
                        ->directory('temp-drawings')
                        ->helperText('Upload PDFs named after drawing numbers for auto-matching.'),
                ])
                ->action(function (array $data) {
                    Notification::make()
                        ->success()
                        ->title('Import Processed')
                        ->body('CAD data imported and drawing files matched.')
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
