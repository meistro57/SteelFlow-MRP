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
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@steelflow.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'settings' => json_encode(['theme' => 'light', 'units' => 'imperial']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Shop Manager',
                'email' => 'manager@steelflow.local',
                'password' => Hash::make('password'),
                'role' => 'user',
                'settings' => json_encode(['theme' => 'light', 'units' => 'imperial']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Grades (expanded with GradeSeeder)
        $this->call(GradeSeeder::class);

        // Seed Materials from AISC database (1200+ shapes)
        $this->call(MaterialSeeder::class);

        // Seed UPF (FabTrol compatible) data
        $this->call(\Modules\UPF\Database\Seeders\UPFDatabaseSeeder::class);

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
            [
                'code' => 'METRO',
                'name' => 'Metro Steel Erectors',
                'address_1' => '789 Main Street',
                'address_2' => null,
                'city' => 'Columbus',
                'state' => 'OH',
                'zip' => '43215',
                'country' => 'USA',
                'phone' => '614-555-0300',
                'email' => 'info@metrosteel.com',
                'notes' => 'Commercial building erector',
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
            [
                'code' => 'MIDWEST',
                'name' => 'Midwest Steel Service',
                'address_1' => '500 Industrial Park',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip' => '60601',
                'phone' => '312-555-0500',
                'fax' => '312-555-0501',
                'email' => 'orders@midweststeel.com',
                'contact_name' => 'Mike Thompson',
                'payment_terms' => 'NET 30',
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
        $customerMetro = DB::table('customers')->where('code', 'METRO')->first()->id;

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
            [
                'job_number' => 'J2024-003',
                'name' => 'Metro Plaza Parking Structure',
                'customer_id' => $customerMetro,
                'status' => 'active',
                'job_type' => 'Parking Structure',
                'po_number' => 'METRO-2024-15',
                'contract_weight_lbs' => 220000.00,
                'contract_weight_kg' => 99790.00,
                'ship_date' => now()->addDays(120),
                'notes' => 'Multi-level parking garage - phased delivery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Seed Phases for Projects
        $project1 = DB::table('projects')->where('job_number', 'J2024-001')->first()->id;
        $project2 = DB::table('projects')->where('job_number', 'J2024-002')->first()->id;
        $project3 = DB::table('projects')->where('job_number', 'J2024-003')->first()->id;

        DB::table('phases')->insert([
            ['project_id' => $project1, 'code' => 'P1', 'description' => 'Main Structure', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project1, 'code' => 'P2', 'description' => 'Secondary Framing', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project2, 'code' => 'MAIN', 'description' => 'Expansion Joint Assembly', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project3, 'code' => 'L1', 'description' => 'Level 1 - Ground', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project3, 'code' => 'L2', 'description' => 'Level 2', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project3, 'code' => 'L3', 'description' => 'Level 3 - Roof', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Lots
        $phase1 = DB::table('phases')->where('project_id', $project1)->where('code', 'P1')->first()->id;
        $phase2 = DB::table('phases')->where('project_id', $project1)->where('code', 'P2')->first()->id;
        $phase3 = DB::table('phases')->where('project_id', $project2)->where('code', 'MAIN')->first()->id;
        $phase4 = DB::table('phases')->where('project_id', $project3)->where('code', 'L1')->first()->id;
        $phase5 = DB::table('phases')->where('project_id', $project3)->where('code', 'L2')->first()->id;

        DB::table('lots')->insert([
            ['project_id' => $project1, 'phase_id' => $phase1, 'code' => 'L1', 'description' => 'Columns', 'ship_date' => now()->addDays(75), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project1, 'phase_id' => $phase1, 'code' => 'L2', 'description' => 'Beams', 'ship_date' => now()->addDays(80), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project1, 'phase_id' => $phase2, 'code' => 'L3', 'description' => 'Bracing', 'ship_date' => now()->addDays(85), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project2, 'phase_id' => $phase3, 'code' => 'SHIP1', 'description' => 'First Shipment', 'ship_date' => now()->addDays(60), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project3, 'phase_id' => $phase4, 'code' => 'COL-1', 'description' => 'Ground Columns', 'ship_date' => now()->addDays(100), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project3, 'phase_id' => $phase4, 'code' => 'BM-1', 'description' => 'Ground Beams', 'ship_date' => now()->addDays(105), 'created_at' => now(), 'updated_at' => now()],
            ['project_id' => $project3, 'phase_id' => $phase5, 'code' => 'COL-2', 'description' => 'Level 2 Columns', 'ship_date' => now()->addDays(110), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed Stock Items with realistic inventory
        $this->call(StockItemSeeder::class);

        $this->command->info('');
        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Default users created:');
        $this->command->info('  - admin@steelflow.local / password');
        $this->command->info('  - manager@steelflow.local / password');
    }
}
