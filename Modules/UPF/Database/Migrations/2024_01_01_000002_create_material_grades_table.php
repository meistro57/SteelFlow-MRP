<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FabTrol equivalent: GRDETAIL_BASE
     * Purpose: Master grade definitions per material type
     */
    public function up(): void
    {
        Schema::create('material_grades', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to material_types
            $table->foreignId('material_type_id')->constrained('material_types')->onDelete('cascade');
            
            // Core fields from FabTrol GRDETAIL_BASE
            $table->string('type', 50)->index(); // Denormalized for quick lookups
            $table->string('grade_name', 100);
            $table->text('description')->nullable();
            
            // FabTrol PKEY system
            $table->string('pkey', 50)->unique()->index();
            
            // Grade specifications
            $table->decimal('yield_strength', 10, 2)->nullable();
            $table->decimal('tensile_strength', 10, 2)->nullable();
            $table->decimal('density', 10, 4)->nullable();
            $table->string('astm_spec', 50)->nullable();
            
            // Pricing multipliers
            $table->decimal('price_multiplier', 8, 4)->default(1.0000);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            
            // Additional properties
            $table->json('properties')->nullable(); // For custom grade properties
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint: one grade name per type
            $table->unique(['material_type_id', 'grade_name'], 'unique_grade_per_type');
            $table->index(['type', 'grade_name'], 'type_grade_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_grades');
    }
};
