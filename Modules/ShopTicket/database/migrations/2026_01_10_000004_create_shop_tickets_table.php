<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_tickets', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('ticket_number')->unique();
            $blueprint->foreignId('project_id')->constrained()->cascadeOnDelete();
            
            // Reference to Assembly or Part Instance (One must be present)
            $blueprint->foreignId('assembly_instance_id')->nullable()->constrained()->nullOnDelete();
            $blueprint->foreignId('part_instance_id')->nullable()->constrained()->nullOnDelete();
            
            $blueprint->string('status')->default('draft');
            $blueprint->integer('priority')->default(0);
            $blueprint->date('due_date')->nullable();
            $blueprint->text('notes')->nullable();
            
            $blueprint->foreignId('created_by')->constrained('users');
            $blueprint->foreignId('assigned_to_employee_id')->nullable()->constrained('employees');
            
            $blueprint->timestamp('released_at')->nullable();
            $blueprint->timestamp('completed_at')->nullable();
            
            $blueprint->timestamps();
            $blueprint->softDeletes();
            
            $blueprint->index(['ticket_number', 'status', 'project_id']);
        });

        Schema::create('shop_ticket_steps', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('shop_ticket_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('work_area_id')->constrained();
            
            $blueprint->integer('sequence')->default(1);
            $blueprint->string('status')->default('pending');
            
            $blueprint->decimal('estimated_hours', 8, 2)->default(0);
            $blueprint->decimal('actual_hours', 8, 2)->default(0);
            
            $blueprint->timestamp('started_at')->nullable();
            $blueprint->timestamp('completed_at')->nullable();
            $blueprint->foreignId('completed_by')->nullable()->constrained('employees');
            
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
            
            $blueprint->index(['shop_ticket_id', 'work_area_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_ticket_steps');
        Schema::dropIfExists('shop_tickets');
    }
};
