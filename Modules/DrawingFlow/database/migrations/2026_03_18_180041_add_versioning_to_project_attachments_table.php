<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_attachments', function (Blueprint $table) {
            $table->string('document_key')->nullable()->after('original_filename');
            $table->unsignedInteger('version_number')->default(1)->after('document_key');
            $table->boolean('is_latest')->default(true)->after('version_number');
        });

        DB::table('project_attachments')->update([
            'document_key' => DB::raw('LOWER(original_filename)'),
            'version_number' => 1,
            'is_latest' => true,
        ]);

        Schema::table('project_attachments', function (Blueprint $table) {
            $table->unique(['project_id', 'document_key', 'version_number'], 'project_attachments_project_doc_ver_unique');
            $table->index(['project_id', 'document_key', 'is_latest'], 'project_attachments_project_doc_latest_index');
        });
    }

    public function down(): void
    {
        Schema::table('project_attachments', function (Blueprint $table) {
            $table->dropUnique('project_attachments_project_doc_ver_unique');
            $table->dropIndex('project_attachments_project_doc_latest_index');
            $table->dropColumn(['document_key', 'version_number', 'is_latest']);
        });
    }
};
