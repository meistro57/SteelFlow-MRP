<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('applications_for_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->string('application_number');
            $table->date('application_date');
            $table->decimal('total_value', 15, 2);
            $table->decimal('percent_complete', 5, 2);
            $table->decimal('retainage_rate', 5, 2)->default(10.00);
            $table->decimal('retainage_amount', 15, 2);
            $table->decimal('previous_payments', 15, 2)->default(0);
            $table->decimal('current_payment_due', 15, 2);
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('schedule_of_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->string('item_name');
            $table->string('description')->nullable();
            $table->decimal('scheduled_value', 15, 2);
            $table->timestamps();
        });

        Schema::create('application_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id', 'afp_id')->constrained('applications_for_payment')->onDelete('cascade');
            $table->foreignId('sov_id')->constrained('schedule_of_values');
            $table->decimal('percent_complete', 5, 2);
            $table->decimal('total_earned', 15, 2);
            $table->decimal('previous_earned', 15, 2);
            $table->decimal('current_earned', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_line_items');
        Schema::dropIfExists('schedule_of_values');
        Schema::dropIfExists('applications_for_payment');
    }
};
