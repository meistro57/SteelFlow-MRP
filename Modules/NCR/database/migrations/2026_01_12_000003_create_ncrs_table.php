<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ncrs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('number')->unique();

            // Link to the failed item
            $blueprint->nullableMorphs('failurables'); // Simplified name for polymorphic linkage

            // Explicit links for easier querying if morphs aren't preferred
            $blueprint->foreignId('production_item_id')->nullable()->constrained('production_items');
            $blueprint->foreignId('part_instance_id')->nullable()->constrained('part_instances');

            // Material quarantine
            $blueprint->foreignId('stock_item_id')->nullable()->constrained('stock_items');

            $blueprint->string('status');
            $blueprint->string('disposition')->nullable(); // SCRAP, REWORK, USE_AS_IS

            $blueprint->text('failure_reason');
            $blueprint->text('remediation_notes')->nullable();

            $blueprint->decimal('scrap_cost', 15, 2)->nullable();
            $blueprint->string('rework_operation')->nullable();

            $blueprint->foreignId('reported_by')->constrained('users');
            $blueprint->foreignId('dispositioned_by')->nullable()->constrained('users');
            $blueprint->timestamp('dispositioned_at')->nullable();

            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ncrs');
    }
};
