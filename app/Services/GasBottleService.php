<?php
// app/Services/GasBottleService.php

namespace App\Services;

use App\Models\Customer;
use App\Models\GasBottleInspection;
use App\Models\GasBottleRental;
use App\Models\StockItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class GasBottleService
{
    /**
     * Reserve a bottle for a customer, optionally tying it to a project and expiry.
     */
    public function reserveBottle(StockItem $bottle, Customer $customer, ?Carbon $expiresAt = null, ?int $projectId = null): GasBottleRental
    {
        $this->assertBottleType($bottle);

        if ($bottle->status !== 'available') {
            throw new InvalidArgumentException('Bottle must be available to reserve.');
        }

        return DB::transaction(function () use ($bottle, $customer, $expiresAt, $projectId) {
            $rental = GasBottleRental::create([
                'stock_item_id' => $bottle->id,
                'customer_id' => $customer->id,
                'status' => 'reserved',
                'reservation_expires_at' => $expiresAt,
                'due_back_at' => null,
                'checked_out_at' => null,
                'deposit_cents' => 0,
                'rental_rate_cents' => 0,
                'rental_rate_period' => 'day',
                'notes' => null,
                'created_by' => Auth::id(),
            ]);

            $bottle->update([
                'status' => 'reserved',
                'reserved_project_id' => $projectId,
            ]);

            StockMovement::create([
                'stock_item_id' => $bottle->id,
                'movement_type' => 'reservation',
                'quantity' => 1,
                'from_status' => 'available',
                'to_status' => 'reserved',
                'from_area' => $bottle->stock_area,
                'to_area' => $bottle->stock_area,
                'reference_type' => GasBottleRental::class,
                'reference_id' => $rental->id,
                'notes' => 'Bottle reserved for customer',
                'created_by' => Auth::id(),
            ]);

            return $rental;
        });
    }

    /**
     * Convert a reservation into an active rental, ensuring stock is available.
     */
    public function checkoutBottle(GasBottleRental $rental, Carbon $dueBackAt, int $depositCents, int $rentalRateCents, string $ratePeriod = 'day'): GasBottleRental
    {
        $bottle = $rental->stockItem;
        $this->assertBottleType($bottle);

        if (! in_array($rental->status, ['reserved', 'pending'], true)) {
            throw new InvalidArgumentException('Rental must be reserved or pending to check out.');
        }

        if (! in_array($bottle->status, ['available', 'reserved'], true)) {
            throw new InvalidArgumentException('Bottle is not available for checkout.');
        }

        return DB::transaction(function () use ($rental, $bottle, $dueBackAt, $depositCents, $rentalRateCents, $ratePeriod) {
            $rental->update([
                'status' => 'active',
                'checked_out_at' => now(),
                'due_back_at' => $dueBackAt,
                'deposit_cents' => $depositCents,
                'rental_rate_cents' => $rentalRateCents,
                'rental_rate_period' => $ratePeriod,
            ]);

            $bottle->update([
                'status' => 'checked_out',
                'reserved_project_id' => $rental->stockItem->reserved_project_id,
            ]);

            StockMovement::create([
                'stock_item_id' => $bottle->id,
                'movement_type' => 'checkout',
                'quantity' => 1,
                'from_status' => 'reserved',
                'to_status' => 'checked_out',
                'from_area' => $bottle->stock_area,
                'to_area' => null,
                'reference_type' => GasBottleRental::class,
                'reference_id' => $rental->id,
                'notes' => 'Bottle checked out to customer',
                'created_by' => Auth::id(),
            ]);

            return $rental->fresh();
        });
    }

    /**
     * Return a bottle to stock and close the rental.
     */
    public function returnBottle(GasBottleRental $rental, ?string $notes = null): GasBottleRental
    {
        $bottle = $rental->stockItem;
        $this->assertBottleType($bottle);

        if ($rental->status !== 'active') {
            throw new InvalidArgumentException('Only active rentals can be returned.');
        }

        return DB::transaction(function () use ($rental, $bottle, $notes) {
            $rental->update([
                'status' => 'closed',
                'returned_at' => now(),
                'notes' => $notes,
            ]);

            $bottle->update([
                'status' => 'available',
                'reserved_project_id' => null,
            ]);

            StockMovement::create([
                'stock_item_id' => $bottle->id,
                'movement_type' => 'return',
                'quantity' => 1,
                'from_status' => 'checked_out',
                'to_status' => 'available',
                'from_area' => null,
                'to_area' => $bottle->stock_area,
                'reference_type' => GasBottleRental::class,
                'reference_id' => $rental->id,
                'notes' => 'Bottle returned from customer',
                'created_by' => Auth::id(),
            ]);

            return $rental->fresh();
        });
    }

    /**
     * Swap an active rental bottle for another available unit.
     */
    public function swapBottle(GasBottleRental $rental, StockItem $replacement, ?string $notes = null): GasBottleRental
    {
        $this->assertBottleType($replacement);

        if ($rental->status !== 'active') {
            throw new InvalidArgumentException('Only active rentals can be swapped.');
        }

        if ($replacement->status !== 'available') {
            throw new InvalidArgumentException('Replacement bottle must be available.');
        }

        return DB::transaction(function () use ($rental, $replacement, $notes) {
            // close out the existing rental instance
            $this->returnBottle($rental, $notes ?? 'Swapped for another bottle');

            // create a fresh rental record for the replacement
            $newRental = GasBottleRental::create([
                'stock_item_id' => $replacement->id,
                'customer_id' => $rental->customer_id,
                'status' => 'pending',
                'reservation_expires_at' => null,
                'due_back_at' => $rental->due_back_at,
                'checked_out_at' => null,
                'deposit_cents' => $rental->deposit_cents,
                'rental_rate_cents' => $rental->rental_rate_cents,
                'rental_rate_period' => $rental->rental_rate_period,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            return $this->checkoutBottle(
                $newRental,
                $rental->due_back_at ?? now()->addWeek(),
                $rental->deposit_cents,
                $rental->rental_rate_cents,
                $rental->rental_rate_period
            );
        });
    }

    /**
     * Mark a rental as lost or damaged, keeping audit history.
     */
    public function flagLossOrDamage(GasBottleRental $rental, string $flag, ?string $notes = null): GasBottleRental
    {
        $flag = strtolower($flag);
        if (! in_array($flag, ['lost', 'damaged'], true)) {
            throw new InvalidArgumentException('Flag must be "lost" or "damaged".');
        }

        $bottle = $rental->stockItem;
        $this->assertBottleType($bottle);

        if ($rental->status !== 'active') {
            throw new InvalidArgumentException('Only active rentals can be flagged.');
        }

        return DB::transaction(function () use ($rental, $bottle, $flag, $notes) {
            $update = [
                'status' => $flag,
                'notes' => $notes,
            ];

            if ($flag === 'lost') {
                $update['lost_at'] = now();
            } else {
                $update['damaged_at'] = now();
            }

            $rental->update($update);

            $bottle->update([
                'status' => $flag,
                'reserved_project_id' => null,
            ]);

            StockMovement::create([
                'stock_item_id' => $bottle->id,
                'movement_type' => $flag,
                'quantity' => 1,
                'from_status' => 'checked_out',
                'to_status' => $flag,
                'from_area' => null,
                'to_area' => null,
                'reference_type' => GasBottleRental::class,
                'reference_id' => $rental->id,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            return $rental->fresh();
        });
    }

    /**
     * Schedule an inspection and optionally complete it immediately.
     */
    public function scheduleInspection(StockItem $bottle, Carbon $scheduledFor, ?string $outcome = null, ?string $notes = null): GasBottleInspection
    {
        $this->assertBottleType($bottle);

        return DB::transaction(function () use ($bottle, $scheduledFor, $outcome, $notes) {
            $inspection = GasBottleInspection::create([
                'stock_item_id' => $bottle->id,
                'scheduled_for' => $scheduledFor,
                'completed_at' => $outcome ? now() : null,
                'outcome' => $outcome,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);

            return $inspection;
        });
    }

    /**
     * Safety net to ensure only bottles are processed through this service.
     */
    private function assertBottleType(StockItem $bottle): void
    {
        if ($bottle->type !== 'gas_bottle') {
            throw new RuntimeException('Stock item is not a gas bottle.');
        }
    }
}
