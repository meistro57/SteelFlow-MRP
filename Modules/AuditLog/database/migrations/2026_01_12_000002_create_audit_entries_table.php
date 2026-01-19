<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_entries', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->morphs('auditable');
            $blueprint->foreignId('user_id')->nullable()->constrained('users');
            $blueprint->string('action');
            $blueprint->json('changes')->nullable();
            $blueprint->json('original')->nullable();
            $blueprint->string('ip_address')->nullable();
            $blueprint->string('user_agent')->nullable();
            $blueprint->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_entries');
    }
};
