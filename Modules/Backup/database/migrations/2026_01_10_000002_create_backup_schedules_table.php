<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('type', ['database', 'files', 'full'])->default('database');
            $table->enum('frequency', ['hourly', 'daily', 'weekly', 'monthly'])->default('daily');
            $table->time('time')->default('02:00:00')->comment('Time of day to run backup');
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('retention_days')->default(30)->comment('How long to keep backups');
            $table->boolean('cloud_sync')->default(false)->comment('Sync to cloud storage');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->json('options')->nullable()->comment('Additional configuration options');
            $table->timestamps();
            $table->softDeletes();

            $table->index('enabled');
            $table->index('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
