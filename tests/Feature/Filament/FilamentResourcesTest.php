<?php

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\VendorResource;
use App\Filament\Resources\WorkAreaResource;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\Vendor;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Authorization Tests
|--------------------------------------------------------------------------
*/

describe('admin panel authorization', function () {
    it('denies access to regular users', function () {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    });

    it('allows access to admin users', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertSuccessful();
    });

    it('allows access to manager users', function () {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get('/admin')
            ->assertSuccessful();
    });

    it('allows access to supervisor users', function () {
        $supervisor = User::factory()->supervisor()->create();

        $this->actingAs($supervisor)
            ->get('/admin')
            ->assertSuccessful();
    });
});

/*
|--------------------------------------------------------------------------
| Customer Resource Tests
|--------------------------------------------------------------------------
*/

describe('CustomerResource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can render index page', function () {
        $this->actingAs($this->admin)
            ->get(CustomerResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can render create page', function () {
        $this->actingAs($this->admin)
            ->get(CustomerResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can render edit page', function () {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->get(CustomerResource::getUrl('edit', ['record' => $customer]))
            ->assertSuccessful();
    });

    it('can list customers', function () {
        $customers = Customer::factory()->count(3)->create();

        livewire(CustomerResource\Pages\ListCustomers::class)
            ->assertCanSeeTableRecords($customers);
    });

    it('can create a customer', function () {
        $newData = [
            'code' => 'TST001',
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '555-0100',
            'city' => 'Test City',
            'state' => 'TS',
            'is_active' => true,
        ];

        livewire(CustomerResource\Pages\CreateCustomer::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('customers', [
            'code' => 'TST001',
            'name' => 'Test Customer',
        ]);
    });

    it('can update a customer', function () {
        $customer = Customer::factory()->create();

        livewire(CustomerResource\Pages\EditCustomer::class, [
            'record' => $customer->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Customer Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($customer->refresh()->name)->toBe('Updated Customer Name');
    });

    it('validates required fields on create', function () {
        livewire(CustomerResource\Pages\CreateCustomer::class)
            ->fillForm([
                'name' => '',
                'code' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    });
});

/*
|--------------------------------------------------------------------------
| Project Resource Tests
|--------------------------------------------------------------------------
*/

describe('ProjectResource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can render index page', function () {
        $this->actingAs($this->admin)
            ->get(ProjectResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can render create page', function () {
        $this->actingAs($this->admin)
            ->get(ProjectResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can render edit page', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->admin)
            ->get(ProjectResource::getUrl('edit', ['record' => $project]))
            ->assertSuccessful();
    });

    it('can list projects', function () {
        $projects = Project::factory()->count(3)->create();

        livewire(ProjectResource\Pages\ListProjects::class)
            ->assertCanSeeTableRecords($projects);
    });

    it('can create a project', function () {
        $customer = Customer::factory()->create();

        $newData = [
            'job_number' => 'JOB001',
            'name' => 'Test Project',
            'customer_id' => $customer->id,
            'status' => 'bid',
        ];

        livewire(ProjectResource\Pages\CreateProject::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('projects', [
            'job_number' => 'JOB001',
            'name' => 'Test Project',
        ]);
    });

    it('can update a project', function () {
        $project = Project::factory()->create();

        livewire(ProjectResource\Pages\EditProject::class, [
            'record' => $project->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Project Name',
                'status' => 'active',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($project->refresh())
            ->name->toBe('Updated Project Name')
            ->status->toBe('active');
    });

    it('validates unique job number', function () {
        $existingProject = Project::factory()->create(['job_number' => 'UNIQUE01']);

        livewire(ProjectResource\Pages\CreateProject::class)
            ->fillForm([
                'job_number' => 'UNIQUE01',
                'name' => 'Test',
                'customer_id' => Customer::factory()->create()->id,
                'status' => 'bid',
            ])
            ->call('create')
            ->assertHasFormErrors(['job_number' => 'unique']);
    });

    it('can filter projects by status', function () {
        $activeProjects = Project::factory()->active()->count(2)->create();
        $completeProjects = Project::factory()->complete()->count(2)->create();

        livewire(ProjectResource\Pages\ListProjects::class)
            ->assertCanSeeTableRecords($activeProjects->merge($completeProjects))
            ->filterTable('status', 'active')
            ->assertCanSeeTableRecords($activeProjects)
            ->assertCanNotSeeTableRecords($completeProjects);
    });
});

/*
|--------------------------------------------------------------------------
| Vendor Resource Tests
|--------------------------------------------------------------------------
*/

describe('VendorResource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can render index page', function () {
        $this->actingAs($this->admin)
            ->get(VendorResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can render create page', function () {
        $this->actingAs($this->admin)
            ->get(VendorResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can list vendors', function () {
        $vendors = Vendor::factory()->count(3)->create();

        livewire(VendorResource\Pages\ListVendors::class)
            ->assertCanSeeTableRecords($vendors);
    });

    it('can create a vendor', function () {
        $newData = [
            'code' => 'VEN001',
            'name' => 'Test Vendor',
            'contact_name' => 'John Doe',
            'email' => 'vendor@test.com',
            'phone' => '555-0200',
            'is_active' => true,
        ];

        livewire(VendorResource\Pages\CreateVendor::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vendors', [
            'code' => 'VEN001',
            'name' => 'Test Vendor',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Department Resource Tests
|--------------------------------------------------------------------------
*/

describe('DepartmentResource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can render index page', function () {
        $this->actingAs($this->admin)
            ->get(DepartmentResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can render create page', function () {
        $this->actingAs($this->admin)
            ->get(DepartmentResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can list departments', function () {
        $departments = Department::factory()->count(3)->create();

        livewire(DepartmentResource\Pages\ListDepartments::class)
            ->assertCanSeeTableRecords($departments);
    });

    it('can create a department', function () {
        $newData = [
            'code' => 'FAB',
            'name' => 'Fabrication',
            'sort_order' => 1,
            'is_active' => true,
        ];

        livewire(DepartmentResource\Pages\CreateDepartment::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('departments', [
            'code' => 'FAB',
            'name' => 'Fabrication',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| WorkArea Resource Tests
|--------------------------------------------------------------------------
*/

describe('WorkAreaResource', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('can render index page', function () {
        $this->actingAs($this->admin)
            ->get(WorkAreaResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('can render create page', function () {
        $this->actingAs($this->admin)
            ->get(WorkAreaResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('can list work areas', function () {
        $workAreas = WorkArea::factory()->count(3)->create();

        livewire(WorkAreaResource\Pages\ListWorkAreas::class)
            ->assertCanSeeTableRecords($workAreas);
    });

    it('can create a work area', function () {
        $department = Department::factory()->create();

        $newData = [
            'department_id' => $department->id,
            'code' => 'CUT01',
            'name' => 'Cutting Station 1',
            'sort_order' => 1,
            'is_active' => true,
        ];

        livewire(WorkAreaResource\Pages\CreateWorkArea::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('work_areas', [
            'code' => 'CUT01',
            'name' => 'Cutting Station 1',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Log Viewer Access Tests
|--------------------------------------------------------------------------
*/

describe('log viewer authorization', function () {
    it('denies access to regular users', function () {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/logs')
            ->assertForbidden();
    });

    it('allows access to admin users', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get('/admin/logs');

        // Log viewer might redirect or return 200
        expect($response->status())->toBeIn([200, 302]);
    });

    it('allows access to managers', function () {
        $manager = User::factory()->manager()->create();

        $response = $this->actingAs($manager)
            ->get('/admin/logs');

        expect($response->status())->toBeIn([200, 302]);
    });

    it('allows access to supervisors', function () {
        $supervisor = User::factory()->supervisor()->create();

        $response = $this->actingAs($supervisor)
            ->get('/admin/logs');

        expect($response->status())->toBeIn([200, 302]);
    });
});
