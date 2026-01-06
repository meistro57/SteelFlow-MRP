<?php

// database/migrations/2026_01_06_000000_create_gas_bottle_rentals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gas_bottle_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status')->index();
            $table->timestamp('reservation_expires_at')->nullable();
            $table->timestamp('due_back_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamp('damaged_at')->nullable();
            $table->unsignedBigInteger('deposit_cents')->default(0);
            $table->unsignedBigInteger('rental_rate_cents')->default(0);
            $table->string('rental_rate_period')->default('day');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['stock_item_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_bottle_rentals');
    }
};
