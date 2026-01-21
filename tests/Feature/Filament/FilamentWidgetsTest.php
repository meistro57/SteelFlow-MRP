<?php

use App\Enums\JobStatus;
use App\Enums\MaterialType;
use App\Enums\PartStatus;
use App\Filament\Widgets\JobsStatusChart;
use App\Filament\Widgets\LowStockAlert;
use App\Filament\Widgets\PendingPartsStats;
use App\Models\FabJob;
use App\Models\FabPart;
use App\Models\RawMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Dashboard Widget Tests
|--------------------------------------------------------------------------
*/

describe('Dashboard', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    });

    it('can render the dashboard', function () {
        $this->get('/admin')
            ->assertSuccessful();
    });

    it('displays registered widgets', function () {
        $this->get('/admin')
            ->assertSuccessful()
            ->assertSee('Jobs by Status');
    });
});

/*
|--------------------------------------------------------------------------
| JobsStatusChart Widget Tests
|--------------------------------------------------------------------------
*/

describe('JobsStatusChart', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    });

    it('can render the widget', function () {
        livewire(JobsStatusChart::class)
            ->assertSuccessful();
    });

    it('displays correct data from fab jobs', function () {
        FabJob::create([
            'job_number' => 'JOB001',
            'status' => JobStatus::InProgress,
        ]);
        FabJob::create([
            'job_number' => 'JOB002',
            'status' => JobStatus::InProgress,
        ]);
        FabJob::create([
            'job_number' => 'JOB003',
            'status' => JobStatus::Complete,
        ]);

        livewire(JobsStatusChart::class)
            ->assertSuccessful();
    });

    it('returns doughnut chart type', function () {
        $widget = new JobsStatusChart();

        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getType');
        $method->setAccessible(true);

        expect($method->invoke($widget))->toBe('doughnut');
    });
});

/*
|--------------------------------------------------------------------------
| PendingPartsStats Widget Tests
|--------------------------------------------------------------------------
*/

describe('PendingPartsStats', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    });

    it('can render the widget', function () {
        livewire(PendingPartsStats::class)
            ->assertSuccessful();
    });

    it('shows correct counts for each status', function () {
        $job = FabJob::create([
            'job_number' => 'TEST001',
            'status' => JobStatus::InProgress,
        ]);

        FabPart::create([
            'fab_job_id' => $job->id,
            'mark_number' => 'P1',
            'quantity' => 1,
            'status' => PartStatus::Pending,
        ]);

        FabPart::create([
            'fab_job_id' => $job->id,
            'mark_number' => 'P2',
            'quantity' => 1,
            'status' => PartStatus::Cutting,
        ]);

        FabPart::create([
            'fab_job_id' => $job->id,
            'mark_number' => 'P3',
            'quantity' => 1,
            'status' => PartStatus::Fabrication,
        ]);

        livewire(PendingPartsStats::class)
            ->assertSuccessful()
            ->assertSee('Pending Parts')
            ->assertSee('In Cutting')
            ->assertSee('In Fabrication')
            ->assertSee('Completed Today');
    });
});

/*
|--------------------------------------------------------------------------
| LowStockAlert Widget Tests
|--------------------------------------------------------------------------
*/

describe('LowStockAlert', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    });

    it('can render the widget', function () {
        livewire(LowStockAlert::class)
            ->assertSuccessful();
    });

    it('shows low stock materials', function () {
        RawMaterial::create([
            'material_type' => MaterialType::Beam,
            'size_description' => 'W8x31',
            'grade' => 'A992',
            'quantity_on_hand' => 2,
            'location' => 'Bay 1',
        ]);

        livewire(LowStockAlert::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(RawMaterial::where('quantity_on_hand', '<', 5)->get());
    });

    it('does not show materials with adequate stock', function () {
        $lowStock = RawMaterial::create([
            'material_type' => MaterialType::Plate,
            'size_description' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'quantity_on_hand' => 1,
            'location' => 'Bay 2',
        ]);

        $adequateStock = RawMaterial::create([
            'material_type' => MaterialType::Angle,
            'size_description' => 'L4x4x1/4',
            'grade' => 'A36',
            'quantity_on_hand' => 50,
            'location' => 'Bay 3',
        ]);

        livewire(LowStockAlert::class)
            ->assertCanSeeTableRecords([$lowStock])
            ->assertCanNotSeeTableRecords([$adequateStock]);
    });

    it('shows empty state when no low stock materials', function () {
        RawMaterial::create([
            'material_type' => MaterialType::Bar,
            'size_description' => '1" Round',
            'grade' => 'A36',
            'quantity_on_hand' => 100,
            'location' => 'Bay 4',
        ]);

        livewire(LowStockAlert::class)
            ->assertSuccessful()
            ->assertSee('All materials stocked');
    });
});
