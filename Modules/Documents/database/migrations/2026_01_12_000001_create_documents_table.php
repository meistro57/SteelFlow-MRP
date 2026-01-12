<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('file_path');
            $blueprint->string('file_type')->nullable();
            $blueprint->string('mime_type')->nullable();
            $blueprint->unsignedBigInteger('file_size')->nullable();
            $blueprint->integer('version')->default(1);
            $blueprint->json('metadata')->nullable();
            
            // Polymorphic link to Project, Assembly, Part, etc.
            $blueprint->nullableMorphs('documentable');
            
            // Audit fields
            $blueprint->foreignId('created_by')->nullable()->constrained('users');
            $blueprint->foreignId('updated_by')->nullable()->constrained('users');
            
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        Schema::create('document_versions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('document_id')->constrained()->onDelete('cascade');
            $blueprint->string('file_path');
            $blueprint->integer('version');
            $blueprint->string('notes')->nullable();
            $blueprint->json('metadata')->nullable();
            
            $blueprint->foreignId('created_by')->nullable()->constrained('users');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
    }
};
