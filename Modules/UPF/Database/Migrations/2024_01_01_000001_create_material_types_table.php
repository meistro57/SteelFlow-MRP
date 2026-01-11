<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FabTrol equivalent: FTMATL_BASE
     * Purpose: Material section/type setup table
     */
    public function up(): void
    {
        Schema::create('material_types', function (Blueprint $table) {
            $table->id();
            
            // Core fields from FabTrol FTMATL_BASE
            $table->string('type', 50)->unique()->index();
            $table->string('title', 100);
            $table->text('description')->nullable();
            
            // FabTrol uses PKEY for unique identification
            $table->string('pkey', 50)->unique()->index();
            
            // Status and flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_metric')->default(false);
            
            // Ordering
            $table->integer('display_order')->default(0);
            
            // Additional metadata
            $table->json('metadata')->nullable(); // For any custom fields
            
            $table->timestamps();
            $table->softDeletes(); // Soft delete instead of hard delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_types');
    }
};
