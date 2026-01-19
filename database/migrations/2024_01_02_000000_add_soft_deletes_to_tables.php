<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Define ALL tables that need soft deletes here
        $tables = [
            'projects',
            'assemblies',
            'parts',
            'drawings',
            'purchase_orders', // <--- Make sure this one is here!
            'stock_items',
            'receiving_lines',
        ];

        foreach ($tables as $tableName) {
            // 2. Only check if the table exists first
            if (Schema::hasTable($tableName)) {
                // 3. Then check if the column is missing before adding it
                if (! Schema::hasColumn($tableName, 'deleted_at')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->softDeletes();
                    });
                }
            }
        }
    }
};
