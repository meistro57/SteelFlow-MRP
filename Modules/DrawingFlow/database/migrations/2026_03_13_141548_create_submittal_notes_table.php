<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submittal_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submittal_id')->constrained('drawing_submittals')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->timestamps();

            $table->index('submittal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submittal_notes');
    }
};
