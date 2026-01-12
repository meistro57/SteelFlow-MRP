<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_invoices', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $blueprint->string('invoice_number');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->date('invoice_date');
            $blueprint->string('match_status')->default('unmatched'); // MATCHED, VARIANCE, UNMATCHED
            $blueprint->text('notes')->nullable();
            
            $blueprint->foreignId('created_by')->nullable()->constrained('users');
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        // Add matching status to PO lines for granular tracking if needed
        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->string('match_status')->default('unmatched')->after('extended_price');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_lines', function (Blueprint $table) {
            $table->dropColumn('match_status');
        });
        Schema::dropIfExists('vendor_invoices');
    }
};
