<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    /**
     * Seed the grades table with common steel grades.
     */
    public function run(): void
    {
        $grades = [
            // Carbon Steels
            ['code' => 'A36', 'description' => 'Carbon Structural Steel', 'is_active' => true],
            ['code' => 'A529-50', 'description' => 'High-Strength Carbon Steel Grade 50', 'is_active' => true],

            // High-Strength Low-Alloy (HSLA)
            ['code' => 'A572-50', 'description' => 'HSLA Steel Grade 50 (Most Common)', 'is_active' => true],
            ['code' => 'A572-65', 'description' => 'HSLA Steel Grade 65', 'is_active' => true],

            // Structural Steel
            ['code' => 'A992', 'description' => 'Structural Steel for W-Shapes', 'is_active' => true],

            // HSS/Tubing Grades
            ['code' => 'A500B', 'description' => 'Cold-Formed HSS Grade B (46 ksi)', 'is_active' => true],
            ['code' => 'A500C', 'description' => 'Cold-Formed HSS Grade C (50 ksi)', 'is_active' => true],
            ['code' => 'A1085', 'description' => 'HSS High-Strength (50 ksi min)', 'is_active' => true],

            // Pipe Grades
            ['code' => 'A53B', 'description' => 'Pipe Grade B (35 ksi)', 'is_active' => true],

            // Weathering Steel
            ['code' => 'A588', 'description' => 'Weathering Steel (Cor-Ten)', 'is_active' => true],
            ['code' => 'A847', 'description' => 'Weathering Steel for HSS', 'is_active' => true],

            // High-Strength
            ['code' => 'A514', 'description' => 'Quenched & Tempered Alloy Steel (100 ksi)', 'is_active' => true],

            // Stainless Steel
            ['code' => '304SS', 'description' => 'Stainless Steel 304 (18-8)', 'is_active' => true],
            ['code' => '316SS', 'description' => 'Stainless Steel 316 (Marine Grade)', 'is_active' => true],

            // Plate Grades
            ['code' => 'A283C', 'description' => 'Low Carbon Plate Grade C', 'is_active' => true],
            ['code' => 'A516-70', 'description' => 'Pressure Vessel Plate Grade 70', 'is_active' => true],
        ];

        foreach ($grades as $grade) {
            DB::table('grades')->updateOrInsert(
                ['code' => $grade['code']],
                array_merge($grade, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Seeded ' . count($grades) . ' steel grades');
    }
}
