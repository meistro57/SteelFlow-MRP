<?php

namespace Modules\UPF\Services;

use Modules\UPF\Models\UpfPrice;

class PricingService
{
    public function calculateCost(string $type, string $size, ?string $grade, float $quantity, float $length, float $weight): float
    {
        $priceRecord = UpfPrice::where('type', $type)
            ->where('size', $size)
            ->where(function ($query) use ($grade) {
                if ($grade) {
                    $query->where('grade_name', $grade);
                } else {
                    $query->whereNull('grade_name');
                }
            })->first();

        if (! $priceRecord) {
            return 0.0;
        }

        $unitPrice = (float) $priceRecord->unit_price;
        $baseCost = match (strtoupper($priceRecord->price_unit)) {
            'LB' => $weight * $unitPrice,
            'FT' => $length * $unitPrice,
            'EA', 'PC' => $quantity * $unitPrice,
            'TON' => ($weight / 2000) * $unitPrice,
            default => $weight * $unitPrice,
        };

        return (float) max($baseCost, $priceRecord->minimum_charge ?? 0);
    }
}
