<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_markups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submittal_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();

            $table->integer('page_number');

            $table->enum('markup_type', [
                'circle', 'arrow', 'text', 'highlight', 'stamp', 'dimension', 'rectangle', 'cloud',
                'pen', 'polyline', 'polygon',
            ]);

            $table->json('markup_data');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['submittal_file_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_markups');
    }
};
