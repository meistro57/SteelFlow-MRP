<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FabTrol equivalent: FTPRICE_BASE
     * Purpose: Master material type/size/grade setup table with pricing
     * This is the CORE UPF table that ties everything together
     */
    public function up(): void
    {
        Schema::create('upf_prices', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('material_type_id')->constrained('material_types')->onDelete('cascade');
            $table->foreignId('material_grade_id')->nullable()->constrained('material_grades')->onDelete('set null');

            // Core identifiers from FabTrol
            $table->string('type', 50)->index();
            $table->string('size', 100)->index();
            $table->string('grade_name', 100)->nullable()->index();

            // FabTrol's key system
            $table->string('pkey', 50)->unique()->index();
            $table->unsignedBigInteger('filekey')->unique()->index(); // FabTrol's sequential key
            $table->unsignedBigInteger('orderkey')->index(); // Display/sort order

            // Size specifications
            $table->string('size_description', 200)->nullable();
            $table->decimal('nominal_thickness', 10, 4)->nullable();
            $table->decimal('nominal_width', 10, 4)->nullable();
            $table->decimal('nominal_length', 10, 4)->nullable();
            $table->decimal('weight_per_foot', 10, 4)->nullable();
            $table->decimal('weight_per_unit', 10, 4)->nullable();

            // Pricing
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->string('price_unit', 20)->default('LB'); // LB, FT, EA, TON, etc.
            $table->decimal('minimum_charge', 12, 2)->nullable();

            // Vendor/supplier info
            $table->string('vendor_part_number', 100)->nullable();
            $table->string('preferred_vendor', 100)->nullable();
            $table->decimal('lead_time_days', 8, 2)->nullable();

            // Stock control
            $table->decimal('min_stock_level', 12, 4)->nullable();
            $table->decimal('max_stock_level', 12, 4)->nullable();
            $table->decimal('reorder_point', 12, 4)->nullable();

            // Status flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_stocked')->default(false);
            $table->boolean('allow_fabrication')->default(true);

            // Additional data
            $table->json('specifications')->nullable(); // For custom specs
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Compound indexes for common queries
            $table->index(['type', 'size'], 'type_size_index');
            $table->index(['type', 'size', 'grade_name'], 'type_size_grade_index');
            $table->index(['filekey', 'orderkey'], 'file_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upf_prices');
    }
};
