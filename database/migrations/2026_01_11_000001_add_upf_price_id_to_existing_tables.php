<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->foreignId('upf_price_id')->after('material_id')->nullable()->constrained('upf_prices')->onDelete('set null');
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->foreignId('upf_price_id')->after('material_id')->nullable()->constrained('upf_prices')->onDelete('set null');
        });

        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->foreignId('upf_price_id')->after('material_id')->nullable()->constrained('upf_prices')->onDelete('set null');
        });
        
        Schema::table('nesting_bars', function (Blueprint $table) {
            $table->foreignId('upf_price_id')->after('stock_item_id')->nullable()->constrained('upf_prices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nesting_bars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('upf_price_id');
        });

        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('upf_price_id');
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('upf_price_id');
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('upf_price_id');
        });
    }
};
