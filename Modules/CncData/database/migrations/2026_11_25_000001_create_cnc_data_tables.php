<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cnc_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->string('program_name');
            $table->string('file_path');
            $table->char('file_hash', 64)->index();
            $table->unsignedInteger('file_size');
            $table->string('source', 20)->default('upload'); // upload|import|api
            $table->string('status', 20)->default('pending')->index();

            // Material and process, as read from the program
            $table->string('units', 2)->default('in');
            $table->string('material_name')->nullable();
            $table->decimal('thickness', 8, 4)->nullable()->index();
            $table->decimal('sheet_x', 10, 3)->nullable();
            $table->decimal('sheet_y', 10, 3)->nullable();
            $table->string('process_label')->nullable();
            $table->unsignedSmallInteger('amps')->nullable()->index();

            // Toolpath totals in program units
            $table->decimal('cut_length', 12, 4)->default(0);
            $table->decimal('mark_length', 12, 4)->default(0);
            $table->decimal('travel_length', 12, 4)->default(0);
            $table->decimal('rapid_length', 12, 4)->default(0);
            $table->unsignedInteger('cut_pierces')->default(0);
            $table->unsignedInteger('true_hole_pierces')->default(0);
            $table->unsignedInteger('mark_starts')->default(0);

            // Time: engine estimate, then the calibrated one once runs back it up
            $table->decimal('rapid_rate', 8, 2);
            $table->decimal('est_minutes', 10, 4)->default(0);
            $table->decimal('arc_minutes', 10, 4)->default(0);
            $table->decimal('calibration_factor', 8, 4)->nullable();
            $table->decimal('est_minutes_calibrated', 10, 4)->nullable();
            $table->boolean('estimate_validated')->default(false);

            // Checks
            $table->unsignedSmallInteger('error_count')->default(0);
            $table->unsignedSmallInteger('warning_count')->default(0);
            $table->boolean('chart_feed_ok')->nullable();
            $table->boolean('chart_kerf_ok')->nullable();

            $table->string('schema_version', 40);
            $table->json('analysis');
            $table->timestamp('analyzed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Duplicate uploads are caught in CncProgramService (same hash + project),
            // not by a unique key, so soft-deleted rows don't block a re-upload.
            $table->index(['project_id', 'status']);
        });

        Schema::create('cnc_program_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cnc_program_id')->constrained('cnc_programs')->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained('parts')->nullOnDelete();
            $table->foreignId('assembly_id')->nullable()->constrained('assemblies')->nullOnDelete();
            $table->string('piece_mark');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('source', 20)->default('manual'); // manual|nest_report|filename
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cnc_program_id', 'piece_mark']);
        });

        Schema::create('cnc_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cnc_program_id')->constrained('cnc_programs')->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('actual_minutes', 10, 4);
            $table->decimal('arc_on_minutes', 10, 4)->nullable();
            $table->unsignedInteger('pierce_count')->nullable();
            $table->string('result', 20)->default('complete'); // complete|partial|aborted
            $table->string('source', 20)->default('manual');   // manual|phoenix_log|xpr|time_entry
            $table->decimal('est_minutes_at_run', 10, 4);       // engine estimate when the run was logged
            $table->decimal('variance_pct', 8, 2)->nullable();  // (actual - est) / est × 100
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cnc_process_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->unsignedSmallInteger('amps');
            $table->string('material', 60)->default('Mild Steel');
            $table->decimal('thickness', 8, 4)->nullable(); // null = any thickness at this current
            $table->decimal('cost_per_pierce', 10, 4)->default(0);
            $table->decimal('cost_per_arc_minute', 10, 4)->default(0);
            $table->decimal('machine_rate_per_hour', 10, 2)->default(0);
            $table->date('effective_from')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['amps', 'material', 'thickness']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cnc_process_rates');
        Schema::dropIfExists('cnc_runs');
        Schema::dropIfExists('cnc_program_parts');
        Schema::dropIfExists('cnc_programs');
    }
};
