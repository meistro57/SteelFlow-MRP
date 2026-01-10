<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_exports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('type', 100)->comment('Type of data being exported (projects, inventory, etc.)');
            $table->enum('format', ['csv', 'json', 'xlsx'])->default('csv');
            $table->enum('status', ['in_progress', 'completed', 'failed'])->default('in_progress');
            $table->string('path', 500)->nullable();
            $table->string('cloud_path', 500)->nullable();
            $table->unsignedBigInteger('size')->nullable()->comment('File size in bytes');
            $table->unsignedInteger('row_count')->nullable()->comment('Number of rows exported');
            $table->json('filters')->nullable()->comment('Filters applied to export');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_exports');
    }
};
