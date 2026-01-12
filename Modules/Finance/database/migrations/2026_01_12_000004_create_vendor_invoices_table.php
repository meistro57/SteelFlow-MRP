<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->decimal('amount', 15, 2);
            $table->string('match_status')->default('unmatched'); // unmatched, matched, variance
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_line_id')->constrained();
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('extended_price', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_invoice_lines');
        $table->dropIfExists('vendor_invoices');
    }
};
