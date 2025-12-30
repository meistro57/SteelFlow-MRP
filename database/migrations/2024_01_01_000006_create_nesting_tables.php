<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nestings', function (Blueprint $table) {
            $table->id();
            $table->string('nesting_number', 30)->unique();
            $table->enum('type', ['linear', 'plate']);
            $table->foreignId('project_id')->nullable()->constrained();
            $table->string('material_type', 10);
            $table->string('grade', 20);
            $table->enum('status', ['draft', 'approved', 'verified', 'confirmed', 'cancelled'])->default('draft');
            $table->integer('total_bars')->default(0);
            $table->integer('total_parts')->default(0);
            $table->decimal('total_length', 12, 4)->default(0);
            $table->decimal('used_length', 12, 4)->default(0);
            $table->decimal('waste_length', 12, 4)->default(0);
            $table->decimal('efficiency_percent', 5, 2)->default(0);
            $table->decimal('kerf_allowance', 6, 4)->default(0.125);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable();
            $table->index(['project_id', 'status']);
            $table->timestamps();
        });

        Schema::create('nesting_bars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nesting_id')->constrained()->cascadeOnDelete();
            $table->integer('bar_number');
            $table->foreignId('stock_item_id')->nullable()->constrained();
            $table->boolean('is_purchase')->default(false);
            $table->decimal('length', 12, 4);
            $table->integer('quantity')->default(1);
            $table->decimal('used_length', 12, 4)->nullable();
            $table->decimal('remnant_length', 12, 4)->nullable();
            $table->boolean('is_cut')->default(false);
            $table->timestamp('cut_date')->nullable();
            $table->unique(['nesting_id', 'bar_number']);
            $table->timestamps();
        });

        Schema::create('nesting_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nesting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nesting_bar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_instance_id')->constrained();
            $table->integer('position_on_bar')->nullable();
            $table->decimal('cut_length', 12, 4);
            $table->decimal('start_position', 12, 4)->nullable();
            $table->boolean('is_cut')->default(false);
            $table->foreignId('cut_by')->nullable()->constrained('users');
            $table->timestamp('cut_at')->nullable();
            $table->index('nesting_bar_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nesting_parts');
        Schema::dropIfExists('nesting_bars');
        Schema::dropIfExists('nestings');
    }
};
