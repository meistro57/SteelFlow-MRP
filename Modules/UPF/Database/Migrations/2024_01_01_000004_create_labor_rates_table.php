<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FabTrol equivalent: LABTABLE_BASE, KISSLAB_BASE
     * Purpose: Labor rate tables by material type
     */
    public function up(): void
    {
        Schema::create('labor_rates', function (Blueprint $table) {
            $table->id();

            // Foreign key to material type
            $table->foreignId('material_type_id')->constrained('material_types')->onDelete('cascade');

            // Core fields
            $table->string('type', 50)->index();
            $table->string('operation_code', 50);
            $table->string('operation_name', 100);
            $table->text('description')->nullable();

            // FabTrol PKEY
            $table->string('pkey', 50)->unique()->index();

            // Labor rates
            $table->decimal('rate_per_hour', 10, 2)->default(0);
            $table->decimal('rate_per_unit', 10, 2)->nullable();
            $table->decimal('setup_time_minutes', 10, 2)->default(0);
            $table->decimal('run_time_per_unit', 10, 2)->default(0);

            // Efficiency factors
            $table->decimal('efficiency_factor', 5, 4)->default(1.0000);
            $table->integer('crew_size')->default(1);

            // Equipment
            $table->string('equipment_required', 100)->nullable();
            $table->decimal('equipment_rate_per_hour', 10, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);

            // Additional data
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['material_type_id', 'operation_code'], 'unique_operation_per_type');
            $table->index(['type', 'operation_code'], 'type_operation_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_rates');
    }
};
