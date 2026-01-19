<?php

namespace Modules\Backup\Listeners;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Modules\Backup\Events\DataExportFailed;

class SendDataExportFailedNotification
{
    /**
     * Handle the event.
     */
    public function handle(DataExportFailed $event): void
    {
        $export = $event->export;

        // Send notification to the user who created the export
        if ($export->creator) {
            Notification::make()
                ->danger()
                ->title('Data Export Failed')
                ->body("Export '{$export->name}' has failed. Error: {$event->errorMessage}")
                ->icon('heroicon-o-x-circle')
                ->actions([
                    Action::make('view')
                        ->label('View Details')
                        ->url(route('filament.admin.resources.data-exports.view', $export)),
                ])
                ->sendToDatabase($export->creator);
        }

        // Log the notification
        \Illuminate\Support\Facades\Log::error('Data export failed notification sent', [
            'export_id' => $export->id,
            'export_name' => $export->name,
            'error' => $event->errorMessage,
            'user_id' => $export->created_by,
        ]);
    }
}
