<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');

            $table->boolean('requires_architect_approval')->default(false);
            $table->boolean('requires_engineer_approval')->default(false);
            $table->boolean('requires_gc_approval')->default(true);

            $table->enum('preferred_submittal_method', ['email', 'portal', 'physical'])->default('email');
            $table->string('submittal_email')->nullable();

            $table->integer('approval_sla_days')->default(14);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_workflows');
    }
};
