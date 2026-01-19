<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FabTrol equivalent: FTHRSTON_BASE
     * Purpose: Auto-handling costs per material type
     */
    public function up(): void
    {
        Schema::create('auto_handling', function (Blueprint $table) {
            $table->id();

            // Foreign key to material type
            $table->foreignId('material_type_id')->constrained('material_types')->onDelete('cascade');

            // Core fields
            $table->string('type', 50)->index();
            $table->string('handling_code', 50);
            $table->string('handling_name', 100);
            $table->text('description')->nullable();

            // FabTrol PKEY
            $table->string('pkey', 50)->unique()->index();

            // Handling costs
            $table->decimal('cost_per_ton', 10, 2)->default(0);
            $table->decimal('cost_per_piece', 10, 2)->default(0);
            $table->decimal('cost_per_foot', 10, 2)->default(0);
            $table->decimal('minimum_charge', 10, 2)->nullable();

            // Weight/size thresholds
            $table->decimal('min_weight', 10, 2)->nullable();
            $table->decimal('max_weight', 10, 2)->nullable();
            $table->decimal('min_length', 10, 2)->nullable();
            $table->decimal('max_length', 10, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_apply')->default(false); // Auto-apply based on thresholds
            $table->integer('display_order')->default(0);

            // Additional data
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['material_type_id', 'handling_code'], 'unique_handling_per_type');
            $table->index(['type', 'handling_code'], 'type_handling_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_handling');
    }
};
