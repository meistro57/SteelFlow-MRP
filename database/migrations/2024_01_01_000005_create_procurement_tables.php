<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 20)->unique();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('project_id')->nullable()->constrained(); // NULL if stock order
            $table->enum('status', ['draft', 'sent', 'partial', 'received', 'closed', 'cancelled'])->default('draft');
            $table->date('order_date')->nullable();
            $table->date('expected_date')->nullable();
            $table->text('ship_to_address')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('freight', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number');
            $table->foreignId('material_id')->nullable()->constrained();
            $table->string('type', 10)->nullable();
            $table->string('size', 30)->nullable();
            $table->string('grade', 20)->nullable();
            $table->decimal('length', 12, 4)->nullable();
            $table->integer('quantity');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->decimal('extended_price', 12, 2)->default(0);
            $table->unsignedBigInteger('nesting_id')->nullable(); 
            $table->text('notes')->nullable();
            $table->unique(['purchase_order_id', 'line_number']);
            $table->timestamps();
        });

        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('stock_id', 30)->unique(); // Auto-generated stock ID
            $table->foreignId('material_id')->constrained();
            $table->string('type', 10);
            $table->string('size', 30);
            $table->string('grade', 20);
            $table->decimal('length', 12, 4);
            $table->integer('quantity')->default(1);
            $table->enum('status', ['free', 'assigned', 'committed', 'used'])->default('free');
            $table->foreignId('reserved_project_id')->nullable()->constrained('projects');
            $table->string('stock_area', 50)->nullable();
            $table->string('heat_number', 50)->nullable();
            $table->string('po_number', 20)->nullable();
            $table->string('country_of_origin', 50)->nullable();
            $table->decimal('cost_per_unit', 12, 4)->nullable();
            $table->date('receive_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_remnant')->default(false);
            $table->foreignId('parent_stock_id')->nullable()->constrained('stock_items');
            $table->index(['type', 'grade', 'status']);
            $table->index('reserved_project_id');
            $table->index('heat_number');
            $table->timestamps();
        });

        Schema::create('receiving_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_line_id')->constrained();
            $table->integer('quantity_received');
            $table->date('receive_date');
            $table->string('heat_number', 50)->nullable();
            $table->string('mill_cert_number', 50)->nullable();
            $table->string('country_of_origin', 50)->nullable();
            $table->string('stock_area', 50)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained();
            $table->enum('movement_type', ['receive', 'assign', 'commit', 'use', 'return', 'adjust', 'transfer']);
            $table->integer('quantity');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->string('from_area', 50)->nullable();
            $table->string('to_area', 50)->nullable();
            $table->string('reference_type', 50)->nullable(); // 'nesting', 'po', 'manual'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('receiving_records');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
    }
};
