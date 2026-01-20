<?php

namespace App\Models;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'amount',
        'type',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'occurrences',
        'generated_count',
        'next_due_date',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'type' => TransactionTypeEnum::class,
            'frequency' => RecurringFrequencyEnum::class,
            'interval' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'occurrences' => 'integer',
            'generated_count' => 'integer',
            'next_due_date' => 'date',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recurring_transaction_tag');
    }
}
