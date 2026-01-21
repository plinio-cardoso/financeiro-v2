<?php

namespace App\Services;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class TransactionService
{
    /**
     * Create a new recurring transaction
     */
    public function createRecurringTransaction(array $data): RecurringTransaction
    {
        $recurring = RecurringTransaction::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'] ?? TransactionTypeEnum::Debit,
            'frequency' => $data['frequency'],
            'interval' => $data['interval'] ?? 1,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'occurrences' => $data['occurrences'] ?? null,
            'active' => true,
            'next_due_date' => $data['start_date'],
        ]);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $recurring->tags()->sync($data['tags']);
        }

        // If start date is today or in the past, let the scheduled job pick it up
        // OR we can generate the first one immediately.
        // Let's generate immediately to give instant feedback to the user.
        // Generate transactions for the rest of this month + all of next month
        // This ensures the user has a correct forecast.
        $targetDate = now()->addMonth()->endOfMonth();
        $daysToGenerate = now()->diffInDays($targetDate);

        Artisan::call('app:generate-transactions', [
            '--days' => max(0, (int) $daysToGenerate),
        ]);

        // Reload to get updated next_due_date
        $recurring->refresh();

        return $recurring;
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(array $data): Transaction
    {
        $transaction = Transaction::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'] ?? TransactionTypeEnum::Debit,
            'status' => $data['status'],
            'due_date' => $data['due_date'],
            'paid_at' => $data['paid_at'] ?? null,
        ]);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $transaction->tags()->sync($data['tags']);
        }

        return $transaction->load('tags');
    }

    /**
     * Update an existing transaction
     */
    public function updateTransaction(int|Transaction $transaction, array $data): Transaction
    {
        if (is_int($transaction)) {
            $transaction = Transaction::findOrFail($transaction);
        }

        $updateData = [];

        if (array_key_exists('title', $data)) {
            $updateData['title'] = $data['title'];
        }
        if (array_key_exists('amount', $data)) {
            $updateData['amount'] = $data['amount'];
        }
        if (array_key_exists('type', $data)) {
            $updateData['type'] = $data['type'];
        }
        if (array_key_exists('status', $data)) {
            $updateData['status'] = $data['status'];
        }
        if (array_key_exists('due_date', $data)) {
            $updateData['due_date'] = $data['due_date'];
        }
        if (array_key_exists('paid_at', $data)) {
            $updateData['paid_at'] = $data['paid_at'];
        }

        $transaction->update($updateData);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $transaction->tags()->sync($data['tags']);
        }

        return $transaction->load('tags');
    }

    /**
     * Delete a transaction
     */
    public function deleteTransaction(int|Transaction $transaction): bool
    {
        if (is_int($transaction)) {
            $transaction = Transaction::findOrFail($transaction);
        }

        return $transaction->delete();
    }

    /**
     * Handle changes to recurrence schedule (frequency, interval, start date)
     */
    public function handleRecurrenceScheduleChange(RecurringTransaction $recurring): void
    {
        // 1. Delete future pending transactions
        $recurring->transactions()
            ->where('status', TransactionStatusEnum::Pending)
            ->where('due_date', '>=', today())
            ->delete();

        // 2. Recalculate next_due_date
        // We need to find the next occurrence relative to start_date that is >= today
        // If start_date is in the future, that's the next due date.
        // If start_date is in the past, we calculate forward.

        $startDate = \Carbon\Carbon::parse($recurring->start_date);
        $nextDate = $startDate->copy();

        if ($nextDate->isPast()) {
            // Simulate progression until we reach today or future
            while ($nextDate->lt(today())) {
                $nextDate = match ($recurring->frequency) {
                    RecurringFrequencyEnum::Weekly => $nextDate->addWeeks($recurring->interval),
                    RecurringFrequencyEnum::Monthly => $nextDate->addMonths($recurring->interval),
                    RecurringFrequencyEnum::Custom => $nextDate->addDays($recurring->interval),
                };
            }
        }

        // 3. Update recurrence
        $recurring->update([
            'next_due_date' => $nextDate,
            'active' => true, // Ensure it's active if it was finished
        ]);
    }

    /**
     * Delete a recurring transaction and its associated transactions based on option
     */
    public function deleteRecurringTransaction(int|RecurringTransaction $recurring, string $option = 'only_recurrence'): bool
    {
        if (is_int($recurring)) {
            $recurring = RecurringTransaction::findOrFail($recurring);
        }

        switch ($option) {
            case 'all':
                // Delete all transactions linked to this recurrence
                $recurring->transactions()->delete();
                break;
            case 'future':
                // Delete only future pending transactions
                $recurring->transactions()
                    ->where('status', TransactionStatusEnum::Pending)
                    ->where('due_date', '>=', today())
                    ->delete();
                break;
            case 'only_recurrence':
            default:
                // Just delete the recurring rule
                break;
        }

        return $recurring->delete();
    }

    /**
     * Find a transaction by ID for a specific user
     */
    public function findTransactionById(int $transactionId, int $userId): ?Transaction
    {
        return Transaction::where('id', $transactionId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(Transaction $transaction): Transaction
    {
        $transaction->markAsPaid();

        return $transaction->fresh();
    }

    /**
     * Mark transaction as pending
     */
    public function markAsPending(Transaction $transaction): Transaction
    {
        $transaction->markAsPending();

        return $transaction->fresh();
    }

    /**
     * Calculate monthly totals (only debits)
     */
    public function calculateMonthlyTotals(int $userId, int $year, int $month): array
    {
        $transactions = $this->getMonthlyDebits($userId, $year, $month);

        return [
            'total_due' => $transactions->sum('amount'),
            'total_paid' => $transactions->filter(fn($t) => $t->isPaid())->sum('amount'),
            'total_pending' => $transactions->filter(fn($t) => $t->isPending())->sum('amount'),
        ];
    }

    /**
     * Get next month total (only pending debits)
     */
    public function getNextMonthTotal(int $userId, int $year, int $month): float
    {
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        $transactions = $this->getMonthlyDebits($userId, $nextMonth->year, $nextMonth->month)
            ->filter(fn($t) => $t->isPending());

        return $transactions->sum('amount');
    }

    /**
     * Get filtered transactions query
     */
    public function getFilteredTransactions(int $userId, array $filters, bool $includeSelect = true)
    {
        $query = Transaction::where('user_id', $userId);

        if ($includeSelect) {
            $query->select([
                'transactions.id',
                'transactions.title',
                'transactions.amount',
                'transactions.due_date',
                'transactions.paid_at',
                'transactions.status',
                'transactions.type',
                'transactions.recurring_transaction_id',
                'transactions.user_id',
            ])
                ->with([
                    'tags:id,name,color',
                    'recurringTransaction:id,title,frequency',
                ]);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        // Date range filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('due_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('due_date', '<=', $filters['end_date']);
        }

        // Tags filter
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Recurring filter
        if (!empty($filters['recurring'])) {
            if ($filters['recurring'] === 'recurring') {
                $query->whereNotNull('recurring_transaction_id');
            } elseif ($filters['recurring'] === 'not_recurring') {
                $query->whereNull('recurring_transaction_id');
            }
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'due_date';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortBy, $sortDirection)
            ->orderBy('id', 'desc');

        return $query;
    }

    /**
     * Get pending transactions for a user
     */
    public function getPendingTransactions(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)
            ->where('status', TransactionStatusEnum::Pending)
            ->orderBy('due_date')
            ->with('tags')
            ->get();
    }

    /**
     * Get overdue transactions for a user (pending and past due date)
     */
    public function getOverdueTransactions(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)
            ->where('status', TransactionStatusEnum::Pending)
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->with('tags')
            ->get();
    }

    /**
     * Get transactions due today for a user
     */
    public function getTransactionsDueToday(int $userId): Collection
    {
        return Transaction::where('user_id', $userId)
            ->where('status', TransactionStatusEnum::Pending)
            ->whereDate('due_date', today())
            ->with('tags')
            ->get();
    }

    /**
     * Get monthly debits for a user
     */
    private function getMonthlyDebits(int $userId, int $year, int $month): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Transaction::where('user_id', $userId)
            ->where('type', TransactionTypeEnum::Debit)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get();
    }
}
