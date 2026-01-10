<?php

namespace Modules\Backup\Listeners;

use Filament\Notifications\Notification;
use Modules\Backup\Events\BackupCompleted;

class SendBackupCompletedNotification
{
    /**
     * Handle the event.
     */
    public function handle(BackupCompleted $event): void
    {
        $backup = $event->backup;

        // Send notification to the user who created the backup
        if ($backup->creator) {
            Notification::make()
                ->success()
                ->title('Backup Completed')
                ->body("Backup '{$backup->name}' has been completed successfully. Size: {$backup->formatted_size}")
                ->icon('heroicon-o-check-circle')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Backup')
                        ->url(route('filament.admin.resources.backups.view', $backup)),
                ])
                ->sendToDatabase($backup->creator);
        }

        // Log the notification
        \Illuminate\Support\Facades\Log::info('Backup completed notification sent', [
            'backup_id' => $backup->id,
            'backup_name' => $backup->name,
            'user_id' => $backup->created_by,
        ]);
    }
}
