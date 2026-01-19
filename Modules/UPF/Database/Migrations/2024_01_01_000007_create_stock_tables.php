<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FabTrol equivalent: SMAVAIL_BASE, STKMOVE_BASE
     * Purpose: Stock/material availability and movement tracking
     */
    public function up(): void
    {
        // Main stock availability table
        Schema::create('upf_stock_items', function (Blueprint $table) {
            $table->id();

            // Link to UPF
            $table->foreignId('upf_price_id')->constrained('upf_prices')->onDelete('cascade');

            // Material identification (denormalized for speed)
            $table->string('type', 50)->index();
            $table->string('size', 100)->index();
            $table->string('grade_name', 100)->nullable()->index();

            // Location
            $table->string('location', 100)->default('MAIN');
            $table->string('bin_location', 50)->nullable();

            // Quantities
            $table->decimal('quantity_on_hand', 12, 4)->default(0);
            $table->decimal('quantity_available', 12, 4)->default(0); // On hand - allocated
            $table->decimal('quantity_allocated', 12, 4)->default(0);
            $table->decimal('quantity_on_order', 12, 4)->default(0);

            // Unit of measure
            $table->string('uom', 20)->default('LB'); // LB, FT, EA, PC

            // Costing
            $table->decimal('unit_cost', 12, 4)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->string('cost_method', 20)->default('FIFO'); // FIFO, LIFO, AVG

            // Tracking
            $table->date('last_received_date')->nullable();
            $table->date('last_issued_date')->nullable();
            $table->integer('turnover_days')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_negative')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['upf_price_id', 'location'], 'unique_item_per_location');
            $table->index(['type', 'size', 'grade_name'], 'material_lookup');
            $table->index(['location', 'bin_location'], 'location_index');
        });

        // Stock movement history
        Schema::create('upf_stock_movements', function (Blueprint $table) {
            $table->id();

            // Link to stock item
            $table->foreignId('upf_stock_item_id')->constrained('upf_stock_items')->onDelete('cascade');

            // Transaction details
            $table->string('transaction_type', 50); // RECEIPT, ISSUE, TRANSFER, ADJUSTMENT
            $table->string('transaction_number', 50)->nullable()->index();
            $table->date('transaction_date');

            // Quantities
            $table->decimal('quantity', 12, 4);
            $table->decimal('quantity_before', 12, 4);
            $table->decimal('quantity_after', 12, 4);

            // Costing
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();

            // Reference information
            $table->string('reference_type', 50)->nullable(); // PO, JOB, TRANSFER, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number', 50)->nullable();

            // Transfer specific
            $table->string('from_location', 100)->nullable();
            $table->string('to_location', 100)->nullable();

            // User tracking
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['transaction_type', 'transaction_date'], 'transaction_index');
            $table->index(['reference_type', 'reference_id'], 'reference_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upf_stock_movements');
        Schema::dropIfExists('upf_stock_items');
    }
};
