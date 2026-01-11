<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FabTrol equivalent: PUR_BASE, FTPOFILE_BASE
     * Purpose: Purchase order tracking
     */
    public function up(): void
    {
        // Purchase order headers
        Schema::create('upf_purchase_orders', function (Blueprint $table) {
            $table->id();
            
            // PO identification
            $table->string('po_number', 50)->unique()->index();
            $table->date('po_date');
            $table->date('required_date')->nullable();
            $table->date('promised_date')->nullable();
            
            // Vendor information
            $table->string('vendor_code', 50);
            $table->string('vendor_name', 200);
            $table->text('vendor_address')->nullable();
            $table->string('vendor_contact', 100)->nullable();
            $table->string('vendor_phone', 50)->nullable();
            
            // Status
            $table->string('status', 20)->default('OPEN'); // OPEN, PARTIAL, CLOSED, CANCELLED
            $table->integer('status_code')->default(0);
            
            // Delivery
            $table->string('ship_via', 100)->nullable();
            $table->string('fob_point', 100)->nullable();
            $table->string('deliver_to', 200)->nullable();
            
            // Financial
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('freight_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            // Terms
            $table->string('payment_terms', 100)->nullable();
            $table->integer('discount_days')->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            
            // User tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'po_date'], 'status_date_index');
            $table->index(['vendor_code', 'po_date'], 'vendor_date_index');
        });
        
        // Purchase order line items
        Schema::create('upf_purchase_order_items', function (Blueprint $table) {
            $table->id();
            
            // Link to PO header
            $table->foreignId('upf_purchase_order_id')->constrained('upf_purchase_orders')->onDelete('cascade');
            
            // Line item details
            $table->integer('line_number')->default(0);
            
            // Material identification
            $table->foreignId('upf_price_id')->nullable()->constrained('upf_prices')->onDelete('set null');
            $table->string('type', 50);
            $table->string('size', 100);
            $table->string('grade_name', 100)->nullable();
            $table->text('description')->nullable();
            
            // Quantities
            $table->decimal('quantity_ordered', 12, 4)->default(0);
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->decimal('quantity_remaining', 12, 4)->default(0);
            $table->string('uom', 20)->default('LB');
            
            // Pricing
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->decimal('extended_price', 12, 2)->default(0);
            
            // Delivery
            $table->date('required_date')->nullable();
            $table->date('promised_date')->nullable();
            
            // Job/project linking
            $table->string('job_number', 50)->nullable();
            $table->string('phase_code', 50)->nullable();
            
            // Status
            $table->string('status', 20)->default('OPEN'); // OPEN, PARTIAL, CLOSED, CANCELLED
            
            // Receiving tracking
            $table->date('last_received_date')->nullable();
            $table->decimal('last_received_quantity', 12, 4)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['upf_purchase_order_id', 'line_number'], 'po_line_index');
            $table->index(['type', 'size', 'grade_name'], 'material_index');
            $table->index(['job_number', 'status'], 'job_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upf_purchase_order_items');
        Schema::dropIfExists('upf_purchase_orders');
    }
};
