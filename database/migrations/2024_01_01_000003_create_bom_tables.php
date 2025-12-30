<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assemblies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('mark', 20);
            $table->string('description', 255)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('weight_each_lbs', 12, 4)->nullable();
            $table->decimal('weight_each_kg', 12, 4)->nullable();
            $table->decimal('total_weight_lbs', 12, 4)->nullable();
            $table->decimal('total_weight_kg', 12, 4)->nullable();
            $table->string('assembly_type', 50)->nullable();
            $table->string('main_member_type', 10)->nullable();
            $table->string('main_member_size', 30)->nullable();
            $table->string('main_member_grade', 20)->nullable();
            $table->decimal('main_member_length', 12, 4)->nullable();
            $table->unsignedBigInteger('drawing_id')->nullable();
            $table->string('model_id', 100)->nullable(); // Tekla/SDS2 GUID
            $table->unique(['project_id', 'mark']);
            $table->index(['project_id', 'assembly_type']);
            $table->timestamps();
        });

        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 30)->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('description', 255)->nullable();
            $table->enum('status', ['created', 'released', 'in_progress', 'complete', 'cancelled'])->default('created');
            $table->integer('priority')->default(0);
            $table->timestamp('released_at')->nullable();
            $table->unsignedBigInteger('released_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->string('load_number', 30)->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['open', 'ready', 'in_transit', 'delivered', 'cancelled'])->default('open');
            $table->string('destination', 255)->nullable();
            $table->date('ship_date')->nullable();
            $table->string('carrier', 100)->nullable();
            $table->string('truck_number', 50)->nullable();
            $table->string('trailer_number', 50)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->decimal('total_weight_lbs', 12, 2)->nullable();
            $table->decimal('total_weight_kg', 12, 2)->nullable();
            $table->integer('total_pieces')->default(0);
            $table->string('bol_number', 50)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
        });

        Schema::create('assembly_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained();
            $table->foreignId('phase_id')->nullable()->constrained();
            $table->foreignId('lot_id')->nullable()->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('production_batches');
            $table->integer('instance_number');
            $table->enum('status', ['not_started', 'in_progress', 'complete', 'shipped', 'delivered'])->default('not_started');
            $table->foreignId('load_id')->nullable()->constrained();
            $table->decimal('ship_weight_lbs', 12, 4)->nullable();
            $table->decimal('ship_weight_kg', 12, 4)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->unique(['assembly_id', 'instance_number']);
            $table->index(['project_id', 'status']);
            $table->index('batch_id');
            $table->index('load_id');
            $table->timestamps();
        });

        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assembly_id')->constrained()->cascadeOnDelete();
            $table->string('part_mark', 20)->nullable();
            $table->foreignId('material_id')->constrained();
            $table->string('type', 10);
            $table->string('size_imperial', 30)->nullable();
            $table->string('size_metric', 30)->nullable();
            $table->string('grade', 20)->nullable();
            $table->decimal('length', 12, 4)->nullable();
            $table->integer('quantity')->default(1);
            $table->boolean('is_main_member')->default(false);
            $table->decimal('weight_each_lbs', 12, 4)->nullable();
            $table->decimal('weight_each_kg', 12, 4)->nullable();
            $table->decimal('total_weight_lbs', 12, 4)->nullable();
            $table->decimal('total_weight_kg', 12, 4)->nullable();
            $table->string('finish', 50)->nullable();
            $table->text('remarks')->nullable();
            $table->integer('file_number')->nullable();
            $table->string('unique_key', 50)->nullable();
            $table->boolean('nc_data_available')->default(false);
            $table->index(['project_id', 'type', 'grade']);
            $table->index('assembly_id');
            $table->index('unique_key');
            $table->timestamps();
        });

        Schema::create('part_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assembly_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained();
            $table->unsignedBigInteger('nesting_id')->nullable();
            $table->integer('bar_number')->nullable();
            $table->enum('status', ['not_started', 'nested', 'cut', 'complete'])->default('not_started');
            $table->index(['project_id', 'status']);
            $table->index('nesting_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_instances');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('assembly_instances');
        Schema::dropIfExists('loads');
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('assemblies');
    }
};
