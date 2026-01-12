<?php

namespace Modules\NCR\Services;

use App\Models\PartInstance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\NCR\Models\NCR;
use Modules\NCR\States\Dispositioned;
use Modules\NCR\States\Closed;

class NCRService
{
    /**
     * Disposition an NCR.
     */
    public function disposition(NCR $ncr, string $disposition, array $data = []): NCR
    {
        return DB::transaction(function () use ($ncr, $disposition, $data) {
            $ncr->update([
                'disposition' => $disposition,
                'remediation_notes' => $data['notes'] ?? $ncr->remediation_notes,
                'dispositioned_by' => Auth::id(),
                'dispositioned_at' => now(),
            ]);

            $ncr->status->transitionTo(Dispositioned::class);

            match (strtoupper($disposition)) {
                'SCRAP' => $this->handleScrap($ncr, $data),
                'REWORK' => $this->handleRework($ncr, $data),
                'USE_AS_IS' => $this->handleUseAsIs($ncr, $data),
                default => throw new \InvalidArgumentException("Invalid disposition: {$disposition}"),
            };

            return $ncr;
        });
    }

    protected function handleScrap(NCR $ncr, array $data): void
    {
        // 1. Inventory Adjustment
        if ($ncr->stock_item_id) {
            $stockItem = $ncr->stockItem;
            $stockItem->update(['status' => 'scrapped']);
            
            Log::info("NCR Scrap: Material {$stockItem->id} marked as scrapped", [
                'ncr_id' => $ncr->id,
                'stock_item_id' => $stockItem->id
            ]);
        }

        // 2. Production Demand (Remake)
        if ($ncr->part_instance_id) {
            $originalPart = $ncr->partInstance;
            
            // Clone the part instance to trigger a remake
            $remakeItem = $originalPart->replicate();
            $remakeItem->status = 'awaiting_nesting';
            // Assuming we might have a priority field or similar in the future
            $remakeItem->created_at = now();
            $remakeItem->save();

            Log::info("NCR Scrap: Triggered remake for Part Instance {$originalPart->id}", [
                'ncr_id' => $ncr->id,
                'new_part_instance_id' => $remakeItem->id
            ]);
        }
    }

    protected function handleRework(NCR $ncr, array $data): void
    {
        $operation = $data['rework_operation'] ?? 'General Rework';
        
        // Logic to add operation to routing would go here.
        // For now, we update the NCR record.
        $ncr->update(['rework_operation' => $operation]);

        Log::info("NCR Rework: Added rework operation '{$operation}'", ['ncr_id' => $ncr->id]);
    }

    protected function handleUseAsIs(NCR $ncr, array $data): void
    {
        Log::info("NCR Use As Is: Engineering override applied", ['ncr_id' => $ncr->id]);
    }

    /**
     * Close an NCR.
     */
    public function close(NCR $ncr): NCR
    {
        $ncr->status->transitionTo(Closed::class);
        return $ncr;
    }
}
