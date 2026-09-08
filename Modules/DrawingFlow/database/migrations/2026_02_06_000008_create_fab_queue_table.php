<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fab_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submittal_id')->constrained('drawing_submittals');
            $table->foreignId('project_id')->constrained();

            $table->string('queue_number', 50)->unique();

            $table->integer('priority')->default(5);

            $table->text('material_requirements')->nullable();
            $table->boolean('cnc_files_attached')->default(false);

            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();

            $table->enum('status', ['queued', 'in_progress', 'completed', 'on_hold'])->default('queued');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();
            $table->text('shop_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fab_queue');
    }
};
