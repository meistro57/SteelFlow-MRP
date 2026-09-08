<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submittal_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submittal_id')->constrained('drawing_submittals')->onDelete('cascade');

            $table->enum('approval_type', [
                'approved', 'approved_as_noted', 'revise_and_resubmit', 'rejected', 'field_verify_required',
            ]);

            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at');

            $table->string('reviewer_name')->nullable();
            $table->string('reviewer_title', 100)->nullable();
            $table->string('reviewer_company')->nullable();
            $table->string('reviewer_email')->nullable();

            $table->text('approval_notes')->nullable();
            $table->text('conditions')->nullable();

            $table->foreignId('approval_file_id')->nullable()->constrained('submittal_files');
            $table->foreignId('created_by_user_id')->constrained('users');

            $table->timestamps();

            $table->index('approval_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submittal_approvals');
    }
};
