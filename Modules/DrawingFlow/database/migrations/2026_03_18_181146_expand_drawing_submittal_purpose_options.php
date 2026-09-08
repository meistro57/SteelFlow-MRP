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

        DB::statement(<<<'SQL'
            ALTER TABLE drawing_submittals
            MODIFY COLUMN purpose ENUM(
                'for_approval',
                'for_information',
                'for_construction',
                'resubmittal',
                'for_fab',
                'for_material_order',
                'for_pricing',
                'for_field_verification',
                'preliminary'
            ) NOT NULL DEFAULT 'for_approval'
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('drawing_submittals')
            ->where('purpose', 'for_fab')
            ->update(['purpose' => 'for_construction']);

        DB::table('drawing_submittals')
            ->whereIn('purpose', ['for_material_order', 'for_pricing', 'for_field_verification', 'preliminary'])
            ->update(['purpose' => 'for_information']);

        DB::statement(<<<'SQL'
            ALTER TABLE drawing_submittals
            MODIFY COLUMN purpose ENUM(
                'for_approval',
                'for_information',
                'for_construction',
                'resubmittal'
            ) NOT NULL DEFAULT 'for_approval'
        SQL);
    }
};
