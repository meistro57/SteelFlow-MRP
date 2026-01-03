<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Material;

class ReferenceDataService
{
    /**
     * Find a material by type and size.
     */
    public function findMaterial(string $type, string $size)
    {
        return Material::where('type', $type)
            ->where(function ($query) use ($size) {
                $query->where('size_imperial', $size)
                    ->orWhere('size_metric', $size);
            })
            ->first();
    }

    /**
     * Find or create a grade.
     */
    public function findOrCreateGrade(string $code)
    {
        return Grade::firstOrCreate(['code' => $code]);
    }
}
