<?php

namespace Tests\Feature\Controllers;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_requires_authentication(): void
    {
        $response = $this->get(route('settings.notifications.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_displays_form_with_settings(): void
    {
        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response = $this->actingAs($user)->get(route('settings.notifications.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.notifications');
        $response->assertViewHas('settings');
    }

    public function test_edit_creates_default_settings_if_not_exists(): void
    {
        $user = User::factory()->create();

        // Ensure no settings exist
        $this->assertDatabaseCount('notification_settings', 0);

        $response = $this->actingAs($user)->get(route('settings.notifications.edit'));

        $response->assertStatus(200);

        // Check that default settings were created with correct default values
        $this->assertDatabaseHas('notification_settings', [
            'notify_due_today' => true,
            'notify_overdue' => true,
        ]);

        $settings = NotificationSetting::first();
        $this->assertEquals([], $settings->emails);
    }

    public function test_update_requires_authentication(): void
    {
        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response = $this->put(route('settings.notifications.update'), [
            'emails' => ['updated@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_update_validates_emails(): void
    {
        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['invalid-email'], // Invalid email format
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response->assertSessionHasErrors(['emails.0']);
    }

    // Boolean fields are auto-converted in prepareForValidation(), so they always pass validation

    public function test_update_updates_settings(): void
    {
        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['updated@example.com', 'another@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        $response->assertRedirect(route('settings.notifications.edit'));

        $this->assertDatabaseHas('notification_settings', [
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        $settings = NotificationSetting::first();
        $this->assertEquals(['updated@example.com', 'another@example.com'], $settings->emails);
    }

    public function test_update_redirects_with_success_message(): void
    {
        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['updated@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => true,
        ]);

        $response->assertRedirect(route('settings.notifications.edit'));
        $response->assertSessionHas('success', 'Configurações atualizadas com sucesso!');
    }

    public function test_update_requires_at_least_one_email(): void
    {
        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => [], // Empty array should fail validation (min:1)
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $response->assertSessionHasErrors(['emails']);
    }
}
