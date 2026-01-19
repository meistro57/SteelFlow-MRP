<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_shared')->default(false);
            $table->integer('columns')->default(12);
            $table->integer('row_height')->default(80);
            $table->json('settings')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'is_default']);
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->string('widget_type', 50);
            $table->string('title', 100)->nullable();
            $table->integer('grid_x')->default(0);
            $table->integer('grid_y')->default(0);
            $table->integer('grid_w')->default(4);
            $table->integer('grid_h')->default(3);
            $table->integer('min_w')->default(2);
            $table->integer('min_h')->default(2);
            $table->json('config')->nullable();
            $table->json('data_source')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['dashboard_id', 'is_visible']);
            $table->index('widget_type');
        });

        Schema::create('widget_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('category', 50);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('component', 100);
            $table->integer('default_w')->default(4);
            $table->integer('default_h')->default(3);
            $table->integer('min_w')->default(2);
            $table->integer('min_h')->default(2);
            $table->integer('max_w')->nullable();
            $table->integer('max_h')->nullable();
            $table->json('default_config')->nullable();
            $table->json('config_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_templates');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('dashboards');
    }
};
