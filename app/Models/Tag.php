<?php

namespace App\Models;

use App\Models\Accessors\TagAccessorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property \Carbon\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<Transaction> $transactions
 */
class Tag extends Model
{
    use HasFactory;
    use TagAccessorTrait;

    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'color',
    ];

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'transaction_tag')
            ->withTimestamps();
    }

    public function recurringTransactions(): BelongsToMany
    {
        return $this->belongsToMany(RecurringTransaction::class, 'recurring_transaction_tag')
            ->withTimestamps();
    }
}
