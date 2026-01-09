<?php

// database/migrations/2026_01_06_000002_add_updated_by_columns_to_audited_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gas_bottle_inspections', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        });

        Schema::table('gas_bottle_rentals', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('gas_bottle_inspections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
        });

        Schema::table('gas_bottle_rentals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
        });
    }
};
