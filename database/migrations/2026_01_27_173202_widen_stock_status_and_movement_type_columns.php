<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_items MODIFY status VARCHAR(20) NOT NULL DEFAULT 'free'");
        DB::statement("ALTER TABLE stock_movements MODIFY movement_type VARCHAR(30) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_items MODIFY status ENUM('free','assigned','committed','used','scrapped') NOT NULL DEFAULT 'free'");
        DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('receive','assign','commit','use','return','adjust','transfer','release','adjustment_add','adjustment_remove','remnant_create') NOT NULL");
    }
};
