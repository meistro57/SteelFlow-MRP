<?php

namespace Modules\UPF\Services;

use Modules\UPF\Models\SystemConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KeyGenerationService
{
    public function generatePKey(string $prefix = ''): string
    {
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(8));
        return $prefix ? "{$prefix}_{$timestamp}_{$random}" : "{$timestamp}_{$random}";
    }

    public function generateUniquePKey(string $table, string $prefix = '', int $maxAttempts = 10): string
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $pkey = $this->generatePKey($prefix);
            if (!DB::table($table)->where('pkey', $pkey)->exists()) {
                return $pkey;
            }
            usleep(10000);
        }
        throw new \RuntimeException("Unable to generate unique PKEY");
    }

    public function getNextFileKey(): int
    {
        return DB::transaction(function () {
            $config = SystemConfig::lockForUpdate()->firstOrCreate([], ['next_filekey' => 1, 'next_orderkey' => 1]);
            $nextKey = $config->next_filekey;
            $config->increment('next_filekey');
            return $nextKey;
        });
    }

    public function getNextOrderKey(): int
    {
        return DB::transaction(function () {
            $config = SystemConfig::lockForUpdate()->firstOrCreate([], ['next_filekey' => 1, 'next_orderkey' => 1]);
            $nextKey = $config->next_orderkey;
            $config->increment('next_orderkey');
            return $nextKey;
        });
    }
}
