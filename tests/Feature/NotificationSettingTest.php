<?php

namespace Tests\Feature;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_notification_settings_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.notifications.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.notifications');
    }

    public function test_user_can_update_notification_settings_with_checkboxes_enabled(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['test@example.com', 'admin@example.com'],
            'notify_due_today' => '1',
            'notify_overdue' => '1',
        ]);

        $response->assertRedirect(route('settings.notifications.edit'));
        $response->assertSessionHas('success');

        $settings = NotificationSetting::first();
        $this->assertEquals(['test@example.com', 'admin@example.com'], $settings->emails);
        $this->assertTrue($settings->notify_due_today);
        $this->assertTrue($settings->notify_overdue);
    }

    public function test_user_can_update_notification_settings_with_checkboxes_disabled(): void
    {
        $user = User::factory()->create();

        // First create settings with checkboxes enabled
        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => true,
        ]);

        // Now update with checkboxes disabled (not sending them in request)
        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['admin@example.com'],
            // Not sending notify_due_today and notify_overdue = checkboxes unchecked
        ]);

        $response->assertRedirect(route('settings.notifications.edit'));
        $response->assertSessionHas('success');

        $settings = NotificationSetting::first();
        $this->assertEquals(['admin@example.com'], $settings->emails);
        $this->assertFalse($settings->notify_due_today);
        $this->assertFalse($settings->notify_overdue);
    }

    public function test_validation_requires_at_least_one_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => [],
            'notify_due_today' => '1',
            'notify_overdue' => '1',
        ]);

        $response->assertSessionHasErrors('emails');
    }

    public function test_validation_requires_valid_email_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['invalid-email', 'valid@example.com'],
            'notify_due_today' => '1',
            'notify_overdue' => '1',
        ]);

        $response->assertSessionHasErrors('emails.0');
    }

    public function test_validation_limits_maximum_10_emails(): void
    {
        $user = User::factory()->create();

        $emails = [];
        for ($i = 1; $i <= 11; $i++) {
            $emails[] = "email{$i}@example.com";
        }

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => $emails,
            'notify_due_today' => '1',
            'notify_overdue' => '1',
        ]);

        $response->assertSessionHasErrors('emails');
    }

    public function test_notification_settings_creates_record_if_none_exists(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(0, NotificationSetting::count());

        $response = $this->actingAs($user)->put(route('settings.notifications.update'), [
            'emails' => ['new@example.com'],
            'notify_due_today' => '1',
            'notify_overdue' => '0',
        ]);

        $response->assertRedirect(route('settings.notifications.edit'));

        $this->assertEquals(1, NotificationSetting::count());
        $settings = NotificationSetting::first();
        $this->assertEquals(['new@example.com'], $settings->emails);
        $this->assertTrue($settings->notify_due_today);
        $this->assertFalse($settings->notify_overdue);
    }
}
