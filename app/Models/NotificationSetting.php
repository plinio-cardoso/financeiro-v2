<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<string> $emails
 * @property bool $notify_due_today
 * @property bool $notify_overdue
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationSetting extends Model
{
    protected $fillable = [
        'emails',
        'notify_due_today',
        'notify_overdue',
    ];

    protected function casts(): array
    {
        return [
            'emails' => 'array',
            'notify_due_today' => 'boolean',
            'notify_overdue' => 'boolean',
        ];
    }

    /**
     * Get the single notification settings record
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(
            [],
            [
                'emails' => [],
                'notify_due_today' => true,
                'notify_overdue' => true,
            ]
        );
    }
}
