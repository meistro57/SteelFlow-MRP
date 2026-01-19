<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FabTrol equivalent: FTSYSTEM
     * Purpose: System-wide configuration including next filekey tracking
     */
    public function up(): void
    {
        Schema::create('system_config', function (Blueprint $table) {
            $table->id();

            // FabTrol's FILEKEY tracking
            $table->unsignedBigInteger('next_filekey')->default(1);
            $table->unsignedBigInteger('next_orderkey')->default(1);

            // Company information
            $table->string('company_name', 200)->nullable();
            $table->string('company_address', 500)->nullable();
            $table->string('company_phone', 50)->nullable();
            $table->string('company_email', 100)->nullable();

            // System settings
            $table->boolean('is_metric_default')->default(false);
            $table->string('default_currency', 10)->default('USD');
            $table->string('default_weight_unit', 10)->default('LB');
            $table->string('default_length_unit', 10)->default('FT');

            // Pricing defaults
            $table->decimal('default_markup_percentage', 5, 2)->default(0);
            $table->decimal('default_waste_factor', 5, 4)->default(1.0000);

            // Feature flags
            $table->boolean('enable_auto_pricing')->default(true);
            $table->boolean('enable_stock_tracking')->default(true);
            $table->boolean('enable_po_integration')->default(true);

            // Additional settings as JSON
            $table->json('settings')->nullable();

            $table->timestamps();
        });

        // Insert default record
        DB::table('system_config')->insert([
            'next_filekey' => 1,
            'next_orderkey' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_config');
    }
};
