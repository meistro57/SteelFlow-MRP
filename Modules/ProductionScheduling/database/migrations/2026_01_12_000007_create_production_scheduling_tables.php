<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('machine_type'); // saw|laser|drill|press_brake|welder|grinder
            $table->integer('location_id')->nullable();
            $table->decimal('hourly_capacity', 10, 2)->nullable();
            $table->string('status')->default('operational'); // operational|maintenance|down
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('machine_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->string('material_type'); // HSS, Plate, etc.
            $table->decimal('max_length_ft', 10, 2)->nullable();
            $table->decimal('max_thickness_in', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')->nullable()->constrained();
            $table->integer('production_batch_id')->nullable(); // production_batches might be in a different module
            $table->integer('operation_sequence')->default(1);
            $table->string('operation_type'); // cut|drill|weld|grind|paint
            $table->foreignId('machine_id')->nullable()->constrained();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users');
            $table->decimal('estimated_hours', 10, 2)->default(0);
            $table->decimal('actual_hours', 10, 2)->default(0);
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            $table->string('status')->default('pending'); // pending|scheduled|in_progress|complete|on_hold
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('machine_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->timestamp('scheduled_start');
            $table->timestamp('scheduled_end');
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });

        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->string('maintenance_type'); // routine|repair|calibration
            $table->date('scheduled_date');
            $table->integer('estimated_downtime_hours')->default(0);
            $table->date('completed_date')->nullable();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_schedules');
        Schema::dropIfExists('machine_schedules');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('machine_capabilities');
        Schema::dropIfExists('machines');
    }
};
