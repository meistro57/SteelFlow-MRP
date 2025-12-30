<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('work_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('badge_barcode', 50)->nullable();
            $table->unique(['department_id', 'code']);
            $table->timestamps();
        });

        Schema::create('part_work_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assembly_instance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_area_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('production_batches');
            $table->integer('sequence_number');
            $table->enum('status', ['pending', 'in_progress', 'complete', 'skipped'])->default('pending');
            $table->decimal('estimated_hours', 10, 4)->nullable();
            $table->decimal('actual_hours', 10, 4)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('employees');
            $table->text('notes')->nullable();
            $table->index(['batch_id', 'work_area_id', 'status']);
            $table->index('part_instance_id');
            $table->timestamps();
        });

        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('project_id')->nullable()->constrained();
            $table->foreignId('assembly_id')->nullable()->constrained();
            $table->foreignId('part_work_area_id')->nullable()->constrained();
            $table->foreignId('work_area_id')->nullable()->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('production_batches');
            $table->string('operation_code', 20)->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('hours', 10, 4)->nullable();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->index(['employee_id', 'start_time']);
            $table->index('project_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('part_work_areas');
        Schema::dropIfExists('work_areas');
        Schema::dropIfExists('departments');
    }
};
