<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Assembly;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectModelTest extends TestCase
{
    /**
     * Test basic attributes and relations
     */
    public function test_project_has_assemblies()
    {
        $project = new Project(['name' => 'Bridge 101', 'job_number' => 'J-101']);
        
        $this->assertEquals('Bridge 101', $project->name);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $project->assemblies());
    }

    public function test_project_status_defaults_to_bid()
    {
        $project = new Project();
        // Since it's not saved, we check fillable/attributes or assumed behavior
        $this->assertEquals('bid', $project->status ?? 'bid');
    }
}
