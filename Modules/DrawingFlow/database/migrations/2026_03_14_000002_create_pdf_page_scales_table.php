<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_page_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submittal_file_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('page_number');
            $table->decimal('calibration_distance', 12, 4);
            $table->decimal('real_length', 12, 4);
            $table->string('unit', 20);
            $table->timestamps();

            $table->unique(['submittal_file_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_page_scales');
    }
};
