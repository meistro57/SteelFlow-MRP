<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_reports()
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login/microsoft');
    }

    public function test_authenticated_user_can_access_dashboard_metrics()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Reports/Index'));
    }
}
