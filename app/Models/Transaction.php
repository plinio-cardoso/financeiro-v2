<?php

namespace App\Models;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Accessors\TransactionAccessorTrait;
use App\Models\Actions\TransactionActionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property float $amount
 * @property TransactionTypeEnum $type
 * @property TransactionStatusEnum $status
 * @property \Carbon\Carbon $due_date
 * @property \Carbon\Carbon|null $paid_at
 * @property int|null $recurring_transaction_id
 * @property int|null $sequence
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<Tag> $tags
 * @property-read RecurringTransaction|null $recurringTransaction
 */
class Transaction extends Model
{
    use HasFactory;
    use TransactionAccessorTrait;
    use TransactionActionTrait;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'amount',
        'type',
        'status',
        'due_date',
        'paid_at',
        'recurring_transaction_id',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => TransactionStatusEnum::class,
            'type' => TransactionTypeEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'transaction_tag')
            ->withTimestamps();
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    /**
     * Scope a query to only include transactions due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now()->toDateString());
    }

    /**
     * Scope a query to only include overdue transactions.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->where('status', TransactionStatusEnum::Pending);
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', TransactionStatusEnum::Pending);
    }

    /**
     * Scope a query to only include debit transactions.
     */
    public function scopeDebits($query)
    {
        return $query->where('type', TransactionTypeEnum::Debit);
    }
}
