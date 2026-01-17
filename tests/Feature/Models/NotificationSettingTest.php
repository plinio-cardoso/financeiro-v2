<?php

namespace Tests\Feature\Models;

use App\Models\NotificationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_emails_is_cast_to_array(): void
    {
        $settings = NotificationSetting::create([
            'emails' => ['test1@example.com', 'test2@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => true,
        ]);

        $this->assertIsArray($settings->emails);
        $this->assertCount(2, $settings->emails);
        $this->assertEquals(['test1@example.com', 'test2@example.com'], $settings->emails);
    }

    public function test_get_settings_returns_existing_settings(): void
    {
        $existing = NotificationSetting::create([
            'emails' => ['existing@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        $settings = NotificationSetting::getSettings();

        $this->assertEquals($existing->id, $settings->id);
        $this->assertEquals(['existing@example.com'], $settings->emails);
        $this->assertFalse($settings->notify_due_today);
        $this->assertTrue($settings->notify_overdue);
    }

    public function test_get_settings_creates_new_if_not_exists(): void
    {
        $this->assertDatabaseCount('notification_settings', 0);

        $settings = NotificationSetting::getSettings();

        $this->assertDatabaseCount('notification_settings', 1);
        $this->assertInstanceOf(NotificationSetting::class, $settings);
        $this->assertEquals([], $settings->emails);
        $this->assertTrue($settings->notify_due_today);
        $this->assertTrue($settings->notify_overdue);
    }

    public function test_boolean_fields_are_cast_correctly(): void
    {
        $settings = NotificationSetting::create([
            'emails' => [],
            'notify_due_today' => '1',
            'notify_overdue' => '0',
        ]);

        $this->assertIsBool($settings->notify_due_today);
        $this->assertIsBool($settings->notify_overdue);
        $this->assertTrue($settings->notify_due_today);
        $this->assertFalse($settings->notify_overdue);
    }
}
