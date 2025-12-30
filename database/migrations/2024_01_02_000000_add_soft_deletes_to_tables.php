<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'phases', 'lots', 'assemblies', 'parts', 'drawings', 
            'stock_items', 'purchase_orders', 'purchase_order_lines',
            'production_batches', 'loads', 'assembly_instances', 'part_instances'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'phases', 'lots', 'assemblies', 'parts', 'drawings', 
            'stock_items', 'purchase_orders', 'purchase_order_lines',
            'production_batches', 'loads', 'assembly_instances', 'part_instances'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
