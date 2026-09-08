<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submittal_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submittal_id')->constrained('drawing_submittals')->onDelete('cascade');

            $table->enum('file_type', [
                'drawing', 'calculation', 'specification', 'photo', 'markup', 'approval', 'other',
            ]);

            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();

            $table->integer('version')->default(1);
            $table->boolean('is_current')->default(true);

            $table->foreignId('uploaded_by_user_id')->constrained('users');
            $table->timestamp('uploaded_at')->useCurrent();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_current');
            $table->index('file_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submittal_files');
    }
};
