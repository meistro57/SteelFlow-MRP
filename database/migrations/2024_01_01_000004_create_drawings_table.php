<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drawings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('number', 50);
            $table->string('revision', 10)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->timestamp('revised_at')->nullable();
            $table->unique(['project_id', 'number']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drawings');
    }
};
