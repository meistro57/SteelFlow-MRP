<?php

namespace App\Services\Pricing;

use App\Models\Part;

class WeightCalculator
{
    /**
     * Calculate weights for a part.
     *
     * Imperial: lbs = unit_weight_lbs (per ft) * length (ft) * quantity
     * Metric: kg = unit_weight_kg (per m) * length (m) * quantity
     *
     * Note: Part.length is stored in feet (standard FabTrol convention).
     */
    public function calculatePartWeights(Part $part): array
    {
        $unitWeightLbs = $part->weight_each_lbs ?: ($part->material->unit_weight_lbs ?? 0);
        $unitWeightKg = $part->weight_each_kg ?: ($part->material->unit_weight_kg ?? 0);

        $lengthFt = $part->length;
        $lengthM = $lengthFt * 0.3048;

        $weightEachLbs = $unitWeightLbs * $lengthFt;
        $weightEachKg = $unitWeightKg * $lengthM;

        return [
            'weight_each_lbs' => $weightEachLbs,
            'weight_each_kg' => $weightEachKg,
            'total_weight_lbs' => $weightEachLbs * $part->quantity,
            'total_weight_kg' => $weightEachKg * $part->quantity,
        ];
    }

    /**
     * Calculate total weights for an assembly based on its parts.
     */
    public function calculateAssemblyWeights(array $partsData): array
    {
        $totalLbs = 0;
        $totalKg = 0;

        foreach ($partsData as $part) {
            $totalLbs += $part['total_weight_lbs'] ?? 0;
            $totalKg += $part['total_weight_kg'] ?? 0;
        }

        return [
            'total_weight_lbs' => $totalLbs,
            'total_weight_kg' => $totalKg,
        ];
    }
}
