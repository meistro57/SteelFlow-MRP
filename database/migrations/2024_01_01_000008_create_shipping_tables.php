<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['bol', 'packing_list', 'weight_ticket', 'delivery_receipt']);
            $table->string('file_path', 500);
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('load_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assembly_instance_id')->constrained();
            $table->decimal('weight_lbs', 12, 4)->nullable();
            $table->decimal('weight_kg', 12, 4)->nullable();
            $table->timestamp('loaded_at')->nullable();
            $table->foreignId('loaded_by')->nullable()->constrained('users');
            $table->timestamp('unloaded_at', 500)->nullable();
            $table->text('notes')->nullable();
            $table->unique(['load_id', 'assembly_instance_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_items');
        Schema::dropIfExists('shipping_documents');
    }
};
