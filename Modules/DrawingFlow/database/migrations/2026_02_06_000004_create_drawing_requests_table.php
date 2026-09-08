<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drawing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('requested_by_user_id')->constrained('users');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            $table->enum('drawing_type', [
                'structural', 'misc', 'railings', 'stairs', 'handrail', 'canopy', 'other',
            ]);

            $table->string('job_number', 100)->nullable();
            $table->text('customer_address')->nullable();

            $table->date('required_date')->nullable();
            $table->date('requested_date');

            $table->enum('status', [
                'pending', 'in_progress', 'ready_to_submit', 'submitted', 'approved', 'on_hold', 'cancelled',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drawing_requests');
    }
};
