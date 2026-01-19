<?php

namespace Modules\Backup\Listeners;

use Filament\Notifications\Notification;
use Modules\Backup\Events\DataExportCompleted;

class SendDataExportCompletedNotification
{
    /**
     * Handle the event.
     */
    public function handle(DataExportCompleted $event): void
    {
        $export = $event->export;

        // Send notification to the user who created the export
        if ($export->creator) {
            Notification::make()
                ->success()
                ->title('Data Export Completed')
                ->body("Export '{$export->name}' has been completed successfully. {$export->row_count} records exported. Size: {$export->formatted_size}")
                ->icon('heroicon-o-check-circle')
                ->actions([
                    \Filament\Actions\Action::make('view')
                        ->label('View Export')
                        ->url(route('filament.admin.resources.data-exports.view', $export)),
                    \Filament\Actions\Action::make('download')
                        ->label('Download')
                        ->url(route('filament.admin.resources.data-exports.view', $export)),
                ])
                ->sendToDatabase($export->creator);
        }

        // Log the notification
        \Illuminate\Support\Facades\Log::info('Data export completed notification sent', [
            'export_id' => $export->id,
            'export_name' => $export->name,
            'user_id' => $export->created_by,
        ]);
    }
}
