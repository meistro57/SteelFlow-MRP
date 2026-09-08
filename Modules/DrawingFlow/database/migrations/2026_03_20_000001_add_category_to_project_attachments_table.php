<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_attachments', function (Blueprint $table) {
            $table->string('category')->nullable()->default('other')->after('document_key');
        });
    }

    public function down(): void
    {
        Schema::table('project_attachments', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
