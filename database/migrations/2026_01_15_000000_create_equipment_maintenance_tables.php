<?php

// 2026_01_15_000000_create_equipment_maintenance_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('default_criticality', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('default_usage_unit', ['hours', 'kilometres', 'cycles', 'loads'])->default('hours');
            $table->integer('default_interval_value')->nullable();
            $table->enum('default_interval_unit', ['days', 'hours', 'kilometres', 'cycles', 'loads'])->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_equipment_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('maintenance_equipment_categories');
            $table->string('name', 255);
            $table->string('asset_tag', 100)->nullable()->unique();
            $table->string('serial_number', 100)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('year')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('in_service_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'in_repair', 'out_of_service', 'decommissioned', 'on_hire'])
                ->default('active');
            $table->string('location', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->enum('criticality', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('usage_unit', ['hours', 'kilometres', 'cycles', 'loads'])->default('hours');
            $table->decimal('current_usage', 12, 2)->default(0);
            $table->decimal('next_service_due_usage', 12, 2)->nullable();
            $table->date('next_service_due_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('last_inspection_date')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'criticality']);
        });

        Schema::create('maintenance_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('maintenance_equipment_assets')->cascadeOnDelete();
            $table->date('usage_date');
            $table->decimal('usage_amount', 12, 2);
            $table->decimal('meter_reading', 12, 2)->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['equipment_id', 'usage_date']);
        });

        Schema::create('maintenance_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('maintenance_equipment_assets')->cascadeOnDelete();
            $table->string('title', 255);
            $table->integer('interval_value');
            $table->enum('interval_unit', ['days', 'hours', 'kilometres', 'cycles', 'loads']);
            $table->text('checklist')->nullable();
            $table->text('required_parts')->nullable();
            $table->text('safety_standards')->nullable();
            $table->string('procedure_reference', 255)->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_program_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('maintenance_programs')->cascadeOnDelete();
            $table->integer('sequence')->default(1);
            $table->string('description', 255);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('expected_minutes')->nullable();
            $table->text('reference_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('maintenance_equipment_assets')->cascadeOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users');
            $table->date('inspection_date');
            $table->enum('condition_rating', ['excellent', 'good', 'fair', 'poor', 'critical']);
            $table->enum('compliance_status', ['compliant', 'non_compliant', 'pending']);
            $table->text('findings')->nullable();
            $table->date('next_due_date')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->timestamps();
        });

        Schema::create('maintenance_fault_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('maintenance_equipment_assets')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users');
            $table->dateTime('reported_at');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->text('description');
            $table->text('immediate_action')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('maintenance_equipment_assets')->cascadeOnDelete();
            $table->foreignId('maintenance_program_id')->nullable()->constrained('maintenance_programs');
            $table->foreignId('fault_report_id')->nullable()->constrained('maintenance_fault_reports');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'scheduled', 'in_progress', 'blocked', 'completed', 'cancelled'])
                ->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('requested_by')->nullable()->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->date('scheduled_date')->nullable();
            $table->date('due_date')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('labour_hours', 10, 2)->nullable();
            $table->decimal('downtime_hours', 10, 2)->nullable();
            $table->string('location', 255)->nullable();
            $table->decimal('meter_reading', 12, 2)->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'priority']);
        });

        Schema::create('maintenance_work_order_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('maintenance_work_orders')->cascadeOnDelete();
            $table->string('description', 255);
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('service_type', 100)->nullable();
            $table->string('contact_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('emergency_contact', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('maintenance_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('maintenance_vendors');
            $table->string('name', 255);
            $table->string('part_number', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_point')->nullable();
            $table->integer('reorder_qty')->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->string('inventory_location', 255)->nullable();
            $table->date('last_stocktake_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['category', 'reorder_point']);
        });

        Schema::create('maintenance_part_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('maintenance_work_orders')->cascadeOnDelete();
            $table->foreignId('part_id')->constrained('maintenance_parts');
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('cost_total', 12, 2)->default(0);
            $table->dateTime('used_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('maintenance_compliance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('maintenance_equipment_assets')->cascadeOnDelete();
            $table->string('standard', 255);
            $table->enum('status', ['compliant', 'non_compliant', 'pending', 'expired'])->default('pending');
            $table->date('last_audit_date')->nullable();
            $table->date('next_audit_due')->nullable();
            $table->text('evidence_location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('audited_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_compliance_records');
        Schema::dropIfExists('maintenance_part_usages');
        Schema::dropIfExists('maintenance_parts');
        Schema::dropIfExists('maintenance_vendors');
        Schema::dropIfExists('maintenance_work_order_tasks');
        Schema::dropIfExists('maintenance_work_orders');
        Schema::dropIfExists('maintenance_fault_reports');
        Schema::dropIfExists('maintenance_inspections');
        Schema::dropIfExists('maintenance_program_tasks');
        Schema::dropIfExists('maintenance_programs');
        Schema::dropIfExists('maintenance_usage_logs');
        Schema::dropIfExists('maintenance_equipment_assets');
        Schema::dropIfExists('maintenance_equipment_categories');
    }
};
