<?php

namespace Modules\DrawingFlow\Services;

use Modules\DrawingFlow\Models\DrawingRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DrawingRequestService
{
    /**
     * Create a new drawing request.
     */
    public function create(array $data): DrawingRequest
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($data) {
                    $data['request_number'] = $this->generateRequestNumber();
                    $data['requested_date'] = $data['requested_date'] ?? now()->toDateString();
                    $data['status'] = 'pending';

                    return DrawingRequest::create($data);
                });
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Could not generate a unique drawing request number.');
    }

    /**
     * Assign a drawing request to a user.
     */
    public function assign(DrawingRequest $request, int $userId): void
    {
        $request->update([
            'assigned_to_user_id' => $userId,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Mark a drawing request as ready to submit.
     */
    public function markReadyToSubmit(DrawingRequest $request): void
    {
        if ($request->status !== 'in_progress') {
            throw new \InvalidArgumentException('Request must be in progress to mark as ready.');
        }

        $request->update(['status' => 'ready_to_submit']);
    }

    /**
     * Cancel a drawing request.
     */
    public function cancel(DrawingRequest $request): void
    {
        $request->update(['status' => 'cancelled']);
    }

    /**
     * Put a drawing request on hold.
     */
    public function putOnHold(DrawingRequest $request): void
    {
        $request->update(['status' => 'on_hold']);
    }

    /**
     * Generate a unique request number.
     */
    private function generateRequestNumber(): string
    {
        $year = date('Y');
        $lastRequest = DrawingRequest::where('request_number', 'like', "DR-{$year}-%")
            ->orderByDesc('request_number')
            ->first();

        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->request_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('DR-%s-%04d', $year, $nextNumber);
    }
}
