<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_theme_setting(): void
    {
        $user = User::factory()->create([
            'settings' => [
                'layout_density' => 'comfortable',
                'sidebar_collapsed' => false,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('settings.update'), [
            'theme' => 'dark',
        ]);

        $response->assertRedirect();

        $user->refresh();

        $this->assertSame('dark', $user->settings['theme']);
        $this->assertSame('comfortable', $user->settings['layout_density']);
        $this->assertFalse($user->settings['sidebar_collapsed']);
    }

    public function test_user_can_update_sidebar_setting(): void
    {
        $user = User::factory()->create([
            'settings' => [
                'theme' => 'light',
                'sidebar_collapsed' => false,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('settings.update'), [
            'sidebar_collapsed' => true,
        ]);

        $response->assertRedirect();

        $user->refresh();

        $this->assertTrue($user->settings['sidebar_collapsed']);
        $this->assertSame('light', $user->settings['theme']);
    }

    public function test_user_cannot_set_invalid_theme(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/settings')->actingAs($user)->post(route('settings.update'), [
            'theme' => 'midnight',
        ]);

        $response->assertRedirect('/settings');
        $response->assertSessionHasErrors('theme');
    }
}
