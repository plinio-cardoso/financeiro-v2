<?php

namespace App\Console\Commands;

use App\Services\TransactionService;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-transactions
                            {--days= : Number of days to generate transactions for}
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
    public function handle(TransactionService $transactionService): int
    {
        $daysAhead = $this->option('days');
        $force = $this->option('force');

        $endDate = $daysAhead
            ? now()->addDays((int) $daysAhead)->endOfDay()
            : $transactionService->getGenerationEndDate();

        $this->info("Generating recurring transactions until: {$endDate->format('Y-m-d')}");

        $generatedTransactions = $transactionService->generateRecurringTransactions($endDate, $force);

        if ($generatedTransactions->isEmpty()) {
            $this->info('No new transactions generated.');

            return self::SUCCESS;
        }

        $this->info("Generated {$generatedTransactions->count()} transactions:");

        $generatedTransactions->each(function ($transaction) {
            $dueDate = $transaction->due_date->format('d/m/Y');
            $this->line("- [{$dueDate}] {$transaction->title} (R$ ".number_format($transaction->amount, 2, ',', '.').')');
        });

        return self::SUCCESS;
    }
}
