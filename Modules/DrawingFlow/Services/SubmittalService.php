<?php

namespace Modules\DrawingFlow\Services;

use Modules\DrawingFlow\Models\DrawingRequest;
use Modules\DrawingFlow\Models\DrawingSubmittal;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubmittalService
{
    /**
     * Create a new submittal from a drawing request.
     */
    public function createFromRequest(DrawingRequest $request, int $userId): DrawingSubmittal
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($request, $userId) {
                    $submittal = DrawingSubmittal::create([
                        'submittal_number' => $this->generateSubmittalNumber(),
                        'drawing_request_id' => $request->id,
                        'project_id' => $request->project_id,
                        'customer_id' => $request->customer_id,
                        'revision' => 'A',
                        'submitted_by_user_id' => $userId,
                        'status' => 'draft',
                        'purpose' => 'for_approval',
                    ]);

                    $request->update(['status' => 'in_progress']);

                    return $submittal;
                });
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Could not generate a unique submittal number.');
    }

    /**
     * Submit a drawing for customer approval.
     */
    public function submit(DrawingSubmittal $submittal): bool
    {
        if (! in_array($submittal->status, ['draft', 'ready_to_submit'])) {
            throw new \InvalidArgumentException('Submittal must be in draft or ready_to_submit status.');
        }

        return DB::transaction(function () use ($submittal) {
            $submittal->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $submittal->drawingRequest->update([
                'status' => 'submitted',
            ]);

            return true;
        });
    }

    /**
     * Process an approval response.
     */
    public function processApproval(
        DrawingSubmittal $submittal,
        string $approvalType,
        array $approvalData
    ): void {
        DB::transaction(function () use ($submittal, $approvalType, $approvalData) {
            // Create approval record
            $submittal->approvals()->create([
                'approval_type' => $approvalType,
                'approved_by' => $approvalData['approved_by'] ?? null,
                'approved_at' => $approvalData['approved_at'] ?? now(),
                'reviewer_name' => $approvalData['reviewer_name'] ?? null,
                'reviewer_title' => $approvalData['reviewer_title'] ?? null,
                'reviewer_company' => $approvalData['reviewer_company'] ?? null,
                'reviewer_email' => $approvalData['reviewer_email'] ?? null,
                'approval_notes' => $approvalData['approval_notes'] ?? null,
                'conditions' => $approvalData['conditions'] ?? null,
                'created_by_user_id' => $approvalData['created_by_user_id'],
            ]);

            // Update submittal status
            $submittal->update([
                'status' => $approvalType,
                'approval_received_at' => now(),
                'approval_type' => $approvalType,
                'approval_notes' => $approvalData['approval_notes'] ?? null,
            ]);

            // Handle next steps based on approval type
            if (in_array($approvalType, ['approved', 'approved_as_noted'])) {
                $submittal->drawingRequest->update(['status' => 'approved']);
            }
        });
    }

    /**
     * Create a new revision of a submittal.
     */
    public function createRevision(DrawingSubmittal $submittal): DrawingSubmittal
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($submittal) {
                    $submittal->update(['status' => 'superseded']);

                    $newSubmittal = DrawingSubmittal::create([
                        'submittal_number' => $this->generateSubmittalNumber(),
                        'drawing_request_id' => $submittal->drawing_request_id,
                        'project_id' => $submittal->project_id,
                        'customer_id' => $submittal->customer_id,
                        'revision' => DrawingSubmittal::nextRevision($submittal->revision),
                        'submitted_by_user_id' => $submittal->submitted_by_user_id,
                        'drawing_discipline' => $submittal->drawing_discipline,
                        'purpose' => 'resubmittal',
                        'status' => 'draft',
                    ]);

                    $submittal->drawingRequest->update(['status' => 'in_progress']);

                    return $newSubmittal;
                });
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('Could not generate a unique submittal number.');
    }

    /**
     * Generate a unique submittal number.
     */
    private function generateSubmittalNumber(): string
    {
        $year = date('Y');
        $lastSubmittal = DrawingSubmittal::where('submittal_number', 'like', "SUB-{$year}-%")
            ->orderByDesc('submittal_number')
            ->first();

        if ($lastSubmittal) {
            $lastNumber = (int) substr($lastSubmittal->submittal_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('SUB-%s-%04d', $year, $nextNumber);
    }
}
