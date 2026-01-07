<?php

// database/migrations/2026_01_07_000000_create_production_items_table.php

declare(strict_types=1);

use App\States\ProductionItem\Queued;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('part_number');
            $table->string('status')->default(Queued::class);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_items');
    }
};
