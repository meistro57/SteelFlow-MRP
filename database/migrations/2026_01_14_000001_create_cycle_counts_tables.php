<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cycle_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count_number')->unique();
            $table->enum('status', ['draft', 'in_progress', 'pending_review', 'completed', 'cancelled'])->default('draft');
            $table->string('stock_area')->nullable();
            $table->string('type_filter')->nullable();
            $table->string('grade_filter')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'scheduled_date']);
            $table->index('stock_area');
        });

        Schema::create('cycle_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->integer('expected_quantity');
            $table->integer('counted_quantity')->nullable();
            $table->boolean('is_counted')->default(false);
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->string('variance_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cycle_count_id', 'is_counted']);
            $table->unique(['cycle_count_id', 'stock_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_count_lines');
        Schema::dropIfExists('cycle_counts');
    }
};
