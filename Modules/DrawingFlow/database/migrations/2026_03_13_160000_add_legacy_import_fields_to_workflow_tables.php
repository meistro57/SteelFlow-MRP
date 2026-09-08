<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drawing_requests', function (Blueprint $table): void {
            $table->timestamp('source_modified_at')->nullable()->after('notes');
            $table->string('source_modified_by')->nullable()->after('source_modified_at');
            $table->string('source_created_by')->nullable()->after('source_modified_by');
            $table->string('source_assigned_to')->nullable()->after('source_created_by');
            $table->string('source_discipline')->nullable()->after('source_assigned_to');
            $table->string('source_status')->nullable()->after('source_discipline');
            $table->unsignedInteger('attachments_count')->default(0)->after('source_status');
            $table->text('pointcloud_link')->nullable()->after('attachments_count');
            $table->text('job_link')->nullable()->after('pointcloud_link');
            $table->string('source_image')->nullable()->after('job_link');
            $table->string('import_source')->nullable()->after('source_image');
        });

        Schema::table('drawing_submittals', function (Blueprint $table): void {
            $table->timestamp('source_modified_at')->nullable()->after('internal_notes');
            $table->string('source_modified_by')->nullable()->after('source_modified_at');
            $table->string('source_created_by')->nullable()->after('source_modified_by');
            $table->string('source_status')->nullable()->after('source_created_by');
            $table->string('returned_status')->nullable()->after('source_status');
            $table->boolean('sent_to_customer')->nullable()->after('returned_status');
            $table->text('model_link')->nullable()->after('sent_to_customer');
            $table->string('source_image')->nullable()->after('model_link');
            $table->boolean('mark_to_continue')->nullable()->after('source_image');
            $table->string('import_source')->nullable()->after('mark_to_continue');
        });

        Schema::table('fab_queue', function (Blueprint $table): void {
            $table->date('date_released')->nullable()->after('shop_notes');
            $table->timestamp('source_created_at')->nullable()->after('date_released');
            $table->timestamp('source_modified_at')->nullable()->after('source_created_at');
            $table->string('source_modified_by')->nullable()->after('source_modified_at');
            $table->string('source_status')->nullable()->after('source_modified_by');
            $table->unsignedInteger('attachments_count')->default(0)->after('source_status');
            $table->text('model_link')->nullable()->after('attachments_count');
            $table->string('import_source')->nullable()->after('model_link');
        });
    }

    public function down(): void
    {
        Schema::table('drawing_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'source_modified_at',
                'source_modified_by',
                'source_created_by',
                'source_assigned_to',
                'source_discipline',
                'source_status',
                'attachments_count',
                'pointcloud_link',
                'job_link',
                'source_image',
                'import_source',
            ]);
        });

        Schema::table('drawing_submittals', function (Blueprint $table): void {
            $table->dropColumn([
                'source_modified_at',
                'source_modified_by',
                'source_created_by',
                'source_status',
                'returned_status',
                'sent_to_customer',
                'model_link',
                'source_image',
                'mark_to_continue',
                'import_source',
            ]);
        });

        Schema::table('fab_queue', function (Blueprint $table): void {
            $table->dropColumn([
                'date_released',
                'source_created_at',
                'source_modified_at',
                'source_modified_by',
                'source_status',
                'attachments_count',
                'model_link',
                'import_source',
            ]);
        });
    }
};
