<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE pdf_markups
            MODIFY COLUMN markup_type ENUM(
                'circle',
                'arrow',
                'text',
                'highlight',
                'stamp',
                'dimension',
                'rectangle',
                'cloud',
                'pen',
                'polyline',
                'polygon'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE pdf_markups
            MODIFY COLUMN markup_type ENUM(
                'circle',
                'arrow',
                'text',
                'highlight',
                'stamp',
                'dimension',
                'rectangle',
                'cloud'
            ) NOT NULL
        ");
    }
};
