<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use Illuminate\Database\Seeder;

class NotificationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notification = NotificationSetting::create([
            'emails' => ['admin@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => true,
        ]);
    }
}
