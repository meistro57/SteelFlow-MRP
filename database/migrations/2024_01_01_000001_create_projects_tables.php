<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('job_number', 20)->unique();
            $table->string('name', 255);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['bid', 'active', 'production', 'shipping', 'complete', 'archived'])->default('bid');
            $table->string('job_type', 50)->nullable();
            $table->string('po_number', 50)->nullable();
            $table->decimal('contract_weight_lbs', 12, 2)->nullable();
            $table->decimal('contract_weight_kg', 12, 2)->nullable();
            $table->date('ship_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('description', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->unique(['project_id', 'code']);
            $table->timestamps();
        });

        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained();
            $table->string('code', 20);
            $table->string('description', 255)->nullable();
            $table->date('ship_date')->nullable();
            $table->unique(['project_id', 'code']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
        Schema::dropIfExists('phases');
        Schema::dropIfExists('projects');
    }
};
