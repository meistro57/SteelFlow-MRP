<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fabrication Jobs - Main work orders
        Schema::create('fab_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number', 30)->unique();
            $table->string('customer_name', 255);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('estimating');
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('due_date');
        });

        // Raw Materials - Stock inventory
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_type', 20);
            $table->string('size_description', 100);
            $table->string('grade', 30)->default('A36');
            $table->decimal('length_stock', 12, 4)->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->string('location', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['material_type', 'grade']);
            $table->index('quantity_on_hand');
        });

        // Fabricated Parts - Individual pieces belonging to jobs
        Schema::create('fab_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fab_job_id')->constrained('fab_jobs')->cascadeOnDelete();
            $table->string('mark_number', 30);
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('material_grade', 30)->default('A36');
            $table->decimal('weight_each', 12, 4)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fab_job_id', 'status']);
            $table->index('status');
            $table->unique(['fab_job_id', 'mark_number']);
        });

        // BOM Items - Links parts to raw materials
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fab_part_id')->constrained('fab_parts')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->restrictOnDelete();
            $table->integer('quantity_required')->default(1);
            $table->decimal('cut_length', 12, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('fab_part_id');
            $table->index('raw_material_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('fab_parts');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('fab_jobs');
    }
};
