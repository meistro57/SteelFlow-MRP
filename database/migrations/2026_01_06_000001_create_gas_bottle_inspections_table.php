<?php
// database/migrations/2026_01_06_000001_create_gas_bottle_inspections_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gas_bottle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_for');
            $table->dateTime('completed_at')->nullable();
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['stock_item_id', 'scheduled_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_bottle_inspections');
    }
};
