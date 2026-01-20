<?php

namespace App\Console\Commands;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-transactions
                            {--days=30 : Number of days to generate transactions for}
                            {--force : Force regeneration even if transactions exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate transactions from active recurring transaction rules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysAhead = (int) $this->option('days');
        $force = $this->option('force');
        $endDate = now()->addDays($daysAhead)->endOfDay();

        $this->info("Generating recurring transactions until: {$endDate->format('Y-m-d')}");

        $recurringTransactions = RecurringTransaction::where('active', true)
            ->where('next_due_date', '<=', $endDate)
            ->get();

        if ($recurringTransactions->isEmpty()) {
            $this->info('No recurring transactions to process.');

            return self::SUCCESS;
        }

        $totalGenerated = 0;

        foreach ($recurringTransactions as $recurring) {
            $generated = $this->generateTransactionsForRecurring($recurring, $endDate, $force);
            $totalGenerated += $generated;

            if ($generated > 0) {
                $this->line("  Generated {$generated} transactions for: {$recurring->title}");
            }
        }

        $this->info("Total transactions generated: {$totalGenerated}");

        return self::SUCCESS;
    }

    private function generateTransactionsForRecurring(
        RecurringTransaction $recurring,
        Carbon $endDate,
        bool $force
    ): int {
        $generatedCount = 0;
        $currentDate = $recurring->next_due_date->copy();

        while ($currentDate->lte($endDate)) {
            // Check if we've reached the end
            if ($this->hasReachedEnd($recurring, $currentDate)) {
                $recurring->update(['active' => false]);
                break;
            }

            // Check if transaction already exists (idempotency)
            if (!$force && $this->transactionExists($recurring, $currentDate)) {
                $currentDate = $this->calculateNextDate($currentDate, $recurring);

                continue;
            }

            // Create the transaction
            $transaction = Transaction::create([
                'user_id' => $recurring->user_id,
                'title' => $recurring->title,
                'description' => $recurring->description,
                'amount' => $recurring->amount,
                'type' => $recurring->type,
                'status' => TransactionStatusEnum::Pending,
                'due_date' => $currentDate,
                'recurring_transaction_id' => $recurring->id,
                'sequence' => $recurring->generated_count + 1,
            ]);

            // Sync tags from recurring transaction
            if ($recurring->tags->isNotEmpty()) {
                $transaction->tags()->sync($recurring->tags->pluck('id'));
            }

            $generatedCount++;
            $recurring->increment('generated_count');

            // Move to next occurrence
            $currentDate = $this->calculateNextDate($currentDate, $recurring);
        }

        // Update next_due_date
        if ($recurring->active) {
            $recurring->update(['next_due_date' => $currentDate]);
        }

        return $generatedCount;
    }

    private function hasReachedEnd(RecurringTransaction $recurring, Carbon $currentDate): bool
    {
        // Check if end_date is set and we've passed it
        if ($recurring->end_date && $currentDate->gt($recurring->end_date)) {
            return true;
        }

        // Check if occurrences limit is set and we've reached it
        if ($recurring->occurrences && $recurring->generated_count >= $recurring->occurrences) {
            return true;
        }

        return false;
    }

    private function transactionExists(RecurringTransaction $recurring, Carbon $dueDate): bool
    {
        return Transaction::where('recurring_transaction_id', $recurring->id)
            ->whereDate('due_date', $dueDate->toDateString())
            ->exists();
    }

    private function calculateNextDate(Carbon $currentDate, RecurringTransaction $recurring): Carbon
    {
        $nextDate = $currentDate->copy();

        return match ($recurring->frequency) {
            RecurringFrequencyEnum::Weekly => $nextDate->addWeeks($recurring->interval),
            RecurringFrequencyEnum::Monthly => $nextDate->addMonths($recurring->interval),
            RecurringFrequencyEnum::Custom => $nextDate->addDays($recurring->interval),
        };
    }
}
