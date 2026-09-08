<?php

namespace Modules\DrawingFlow\Notifications;

use Modules\DrawingFlow\Models\DrawingSubmittal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubmittalApprovalRecorded extends Notification
{
    use Queueable;

    public function __construct(
        private DrawingSubmittal $submittal,
        private string $approvalType,
        private string $recordedByName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Submittal approval recorded',
            'message' => sprintf(
                '%s received %s by %s.',
                $this->submittal->submittal_number,
                str_replace('_', ' ', $this->approvalType),
                $this->recordedByName,
            ),
            'submittal_id' => $this->submittal->id,
            'submittal_number' => $this->submittal->submittal_number,
            'approval_type' => $this->approvalType,
            'recorded_by' => $this->recordedByName,
            'url' => route('submittals.show', $this->submittal),
        ];
    }
}
