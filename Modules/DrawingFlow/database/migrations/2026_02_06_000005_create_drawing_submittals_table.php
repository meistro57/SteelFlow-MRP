<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drawing_submittals', function (Blueprint $table) {
            $table->id();
            $table->string('submittal_number', 50)->unique();
            $table->foreignId('drawing_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained();
            $table->foreignId('customer_id')->constrained();

            $table->string('revision', 10)->default('A');

            $table->foreignId('submitted_by_user_id')->constrained('users');
            $table->timestamp('submitted_at')->nullable();

            $table->enum('drawing_discipline', [
                'residential_steel', 'commercial_misc', 'commercial_structural',
                'railings', 'stairs', 'other',
            ])->nullable();

            $table->enum('purpose', [
                'for_approval', 'for_information', 'for_construction', 'resubmittal',
            ])->default('for_approval');

            $table->enum('status', [
                'draft', 'ready_to_submit', 'submitted', 'approved',
                'approved_as_noted', 'revise_and_resubmit', 'rejected',
                'field_verify_required', 'superseded',
            ])->default('draft');

            $table->timestamp('approval_received_at')->nullable();
            $table->string('approval_type', 50)->nullable();
            $table->text('approval_notes')->nullable();

            $table->text('next_action')->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('revision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drawing_submittals');
    }
};
