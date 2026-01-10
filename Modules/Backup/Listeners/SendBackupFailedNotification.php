<?php

namespace Modules\Backup\Listeners;

use Filament\Notifications\Notification;
use Modules\Backup\Events\BackupFailed;

class SendBackupFailedNotification
{
    /**
     * Handle the event.
     */
    public function handle(BackupFailed $event): void
    {
        $backup = $event->backup;

        // Send notification to the user who created the backup
        if ($backup->creator) {
            Notification::make()
                ->danger()
                ->title('Backup Failed')
                ->body("Backup '{$backup->name}' has failed. Error: {$event->errorMessage}")
                ->icon('heroicon-o-x-circle')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Details')
                        ->url(route('filament.admin.resources.backups.view', $backup)),
                ])
                ->sendToDatabase($backup->creator);
        }

        // Also send to all admin users for critical failures
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->id !== $backup->created_by) {
                Notification::make()
                    ->danger()
                    ->title('Backup Failed')
                    ->body("Backup '{$backup->name}' has failed. Error: {$event->errorMessage}")
                    ->icon('heroicon-o-x-circle')
                    ->sendToDatabase($admin);
            }
        }

        // Log the notification
        \Illuminate\Support\Facades\Log::error('Backup failed notification sent', [
            'backup_id' => $backup->id,
            'backup_name' => $backup->name,
            'error' => $event->errorMessage,
            'user_id' => $backup->created_by,
        ]);
    }
}
