<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);
            $table->string('size_imperial', 30)->nullable();
            $table->string('size_metric', 30)->nullable();
            $table->foreignId('grade_id')->constrained();
            $table->decimal('unit_weight_lbs', 10, 4)->nullable();
            $table->decimal('unit_weight_kg', 10, 4)->nullable();
            $table->decimal('price_per_lb', 10, 4)->nullable();
            $table->decimal('price_per_kg', 10, 4)->nullable();
            $table->decimal('surface_area_sqft', 10, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('sort_order', 15, 4)->nullable();
            $table->timestamps();
            $table->index(['type', 'grade_id']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->nullable();
            $table->string('name', 255);
            $table->string('address_1', 255)->nullable();
            $table->string('address_2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('country', 50)->default('USA');
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->nullable();
            $table->string('name', 255);
            $table->string('address_1', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('contact_name', 100)->nullable();
            $table->string('payment_terms', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_code', 20)->unique()->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('department', 50)->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('badge_barcode', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('grades');
    }
};
