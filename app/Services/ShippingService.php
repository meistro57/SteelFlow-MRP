<?php

namespace App\Services;

use App\Models\AssemblyInstance;
use App\Models\Load;
use App\Models\LoadItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShippingService
{
    /**
     * Add an assembly instance to a load.
     */
    public function addItemToLoad(Load $load, AssemblyInstance $instance): LoadItem
    {
        // Eager load assembly to prevent N+1 query
        if (!$instance->relationLoaded('assembly')) {
            $instance->load('assembly');
        }

        return DB::transaction(function () use ($load, $instance) {
            $item = LoadItem::create([
                'load_id' => $load->id,
                'assembly_instance_id' => $instance->id,
                'weight_lbs' => $instance->assembly->weight_each_lbs,
                'weight_kg' => $instance->assembly->weight_each_kg,
                'loaded_at' => now(),
                'loaded_by' => Auth::id(),
            ]);

            // Update assembly instance status
            $instance->update([
                'status' => 'shipped', // Or 'loaded' if pending transit
                'load_id' => $load->id,
            ]);

            // Update load totals
            $load->increment('total_weight_lbs', $item->weight_lbs);
            $load->increment('total_weight_kg', $item->weight_kg);
            $load->increment('total_pieces');

            return $item;
        });
    }

    /**
     * Mark a load as shipped.
     */
    public function shipLoad(Load $load): void
    {
        // Eager load items with assembly instances to prevent N+1 queries
        $load->load('items.assemblyInstance');

        $load->update([
            'status' => 'in_transit',
            'shipped_at' => now(),
        ]);

        // Update all items on the load
        foreach ($load->items as $item) {
            $item->assemblyInstance->update([
                'status' => 'shipped',
                'shipped_at' => now(),
            ]);
        }
    }
}
