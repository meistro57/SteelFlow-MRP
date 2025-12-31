<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Users
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@steelflow.local',
                'password' => Hash::make('password'),
                'settings' => json_encode(['theme' => 'light', 'units' => 'imperial']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shop Manager',
                'email' => 'manager@steelflow.local',
                'password' => Hash::make('password'),
                'settings' => json_encode(['theme' => 'light', 'units' => 'imperial']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Grades
        DB::table('grades')->insert([
            ['code' => 'A36', 'description' => 'Carbon Steel', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'A572-50', 'description' => 'High-Strength Low-Alloy Steel', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'A992', 'description' => 'Structural Steel', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => '304SS', 'description' => 'Stainless Steel 304', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Materials
        $gradeA36 = DB::table('grades')->where('code', 'A36')->first()->id;
        $gradeA572 = DB::table('grades')->where('code', 'A572-50')->first()->id;

        DB::table('materials')->insert([
            [
                'type' => 'PLATE',
                'size_imperial' => '1/2"',
                'size_metric' => '12.7mm',
                'grade_id' => $gradeA36,
                'unit_weight_lbs' => 20.40,
                'unit_weight_kg' => 9.25,
                'price_per_lb' => 0.65,
                'price_per_kg' => 1.43,
                'surface_area_sqft' => 1.0,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'ANGLE',
                'size_imperial' => 'L4x4x1/2',
                'size_metric' => 'L100x100x12',
                'grade_id' => $gradeA36,
                'unit_weight_lbs' => 12.80,
                'unit_weight_kg' => 5.81,
                'price_per_lb' => 0.72,
                'price_per_kg' => 1.59,
                'surface_area_sqft' => null,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'BEAM',
                'size_imperial' => 'W12x26',
                'size_metric' => 'W310x38.7',
                'grade_id' => $gradeA572,
                'unit_weight_lbs' => 26.00,
                'unit_weight_kg' => 11.79,
                'price_per_lb' => 0.85,
                'price_per_kg' => 1.87,
                'surface_area_sqft' => null,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Customers
        DB::table('customers')->insert([
            [
                'code' => 'ACME',
                'name' => 'ACME Construction Corp',
                'address_1' => '123 Industrial Blvd',
                'address_2' => 'Suite 100',
                'city' => 'Pittsburgh',
                'state' => 'PA',
                'zip' => '15222',
                'country' => 'USA',
                'phone' => '412-555-0100',
                'email' => 'orders@acmeconstruction.com',
                'notes' => 'Major steel fabrication customer - NET 30 terms',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BRIDGE',
                'name' => 'Bridge Builders Inc',
                'address_1' => '456 Span Street',
                'address_2' => null,
                'city' => 'Cleveland',
                'state' => 'OH',
                'zip' => '44101',
                'country' => 'USA',
                'phone' => '216-555-0200',
                'email' => 'contracts@bridgebuilders.com',
                'notes' => 'Specializes in bridge structures',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Vendors
        DB::table('vendors')->insert([
            [
                'code' => 'STEEL1',
                'name' => 'National Steel Supply',
                'address_1' => '789 Mill Road',
                'city' => 'Gary',
                'state' => 'IN',
                'zip' => '46402',
                'phone' => '219-555-0300',
                'fax' => '219-555-0301',
                'email' => 'sales@nationalsteel.com',
                'contact_name' => 'John Miller',
                'payment_terms' => 'NET 30',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'METAL2',
                'name' => 'Premium Metals LLC',
                'address_1' => '321 Foundry Ave',
                'city' => 'Detroit',
                'state' => 'MI',
                'zip' => '48201',
                'phone' => '313-555-0400',
                'fax' => '313-555-0401',
                'email' => 'orders@premiummetals.com',
                'contact_name' => 'Sarah Johnson',
                'payment_terms' => 'NET 45',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Employees
        DB::table('employees')->insert([
            [
                'user_id' => 1,
                'employee_code' => 'EMP001',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'department' => 'Management',
                'hourly_rate' => 45.00,
                'is_active' => true,
                'badge_barcode' => 'EMP001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'employee_code' => 'EMP002',
                'first_name' => 'Shop',
                'last_name' => 'Manager',
                'department' => 'Production',
                'hourly_rate' => 35.00,
                'is_active' => true,
                'badge_barcode' => 'EMP002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Departments
        DB::table('departments')->insert([
            ['code' => 'WELD', 'name' => 'Welding', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PAINT', 'name' => 'Paint & Finish', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CUT', 'name' => 'Cutting & Fabrication', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ASSEM', 'name' => 'Assembly', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Work Areas
        $deptWeld = DB::table('departments')->where('code', 'WELD')->first()->id;
        $deptCut = DB::table('departments')->where('code', 'CUT')->first()->id;
        $deptPaint = DB::table('departments')->where('code', 'PAINT')->first()->id;

        DB::table('work_areas')->insert([
            ['department_id' => $deptCut, 'code' => 'SAW', 'name' => 'Saw Station', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['department_id' => $deptWeld, 'code' => 'WELD1', 'name' => 'Welding Bay 1', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['department_id' => $deptWeld, 'code' => 'WELD2', 'name' => 'Welding Bay 2', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['department_id' => $deptPaint, 'code' => 'BLAST', 'name' => 'Sand Blast', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['department_id' => $deptPaint, 'code' => 'SPRAY', 'name' => 'Paint Booth', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Sample Projects
        $customerAcme = DB::table('customers')->where('code', 'ACME')->first()->id;
        $customerBridge = DB::table('customers')->where('code', 'BRIDGE')->first()->id;

        DB::table('projects')->insert([
            [
                'job_number' => 'J2024-001',
                'name' => 'Downtown Office Building - Steel Frame',
                'customer_id' => $customerAcme,
                'status' => 'active',
                'job_type' => 'Building Frame',
                'po_number' => 'PO-ACME-12345',
                'contract_weight_lbs' => 150000.00,
                'contract_weight_kg' => 68039.00,
                'ship_date' => now()->addDays(90),
                'notes' => 'Priority project - requires galvanized finish',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'job_number' => 'J2024-002',
                'name' => 'River Bridge Expansion Joint Assembly',
                'customer_id' => $customerBridge,
                'status' => 'production',
                'job_type' => 'Bridge Component',
                'po_number' => 'BB-2024-Q1',
                'contract_weight_lbs' => 85000.00,
                'contract_weight_kg' => 38555.00,
                'ship_date' => now()->addDays(60),
                'notes' => 'Special inspection requirements per AISC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Phases for Projects
        $project1 = DB::table('projects')->where('job_number', 'J2024-001')->first()->id;
        $project2 = DB::table('projects')->where('job_number', 'J2024-002')->first()->id;

        DB::table('phases')->insert([
            ['project_id' => $project1, 'code' => 'P1', 'description' => 'Main Structure', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project1, 'code' => 'P2', 'description' => 'Secondary Framing', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project2, 'code' => 'MAIN', 'description' => 'Expansion Joint Assembly', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Lots
        $phase1 = DB::table('phases')->where('project_id', $project1)->where('code', 'P1')->first()->id;
        $phase2 = DB::table('phases')->where('project_id', $project1)->where('code', 'P2')->first()->id;
        $phase3 = DB::table('phases')->where('project_id', $project2)->where('code', 'MAIN')->first()->id;

        DB::table('lots')->insert([
            ['project_id' => $project1, 'phase_id' => $phase1, 'code' => 'L1', 'description' => 'Columns', 'ship_date' => now()->addDays(75), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project1, 'phase_id' => $phase1, 'code' => 'L2', 'description' => 'Beams', 'ship_date' => now()->addDays(80), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project1, 'phase_id' => $phase2, 'code' => 'L3', 'description' => 'Bracing', 'ship_date' => now()->addDays(85), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project2, 'phase_id' => $phase3, 'code' => 'SHIP1', 'description' => 'First Shipment', 'ship_date' => now()->addDays(60), 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('📧 Default users created:');
        $this->command->info('   - admin@steelflow.local / password');
        $this->command->info('   - manager@steelflow.local / password');
    }
}
