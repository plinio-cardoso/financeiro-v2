<?php

namespace Tests\Feature;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_monthly_transactions(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->create([
                'user_id' => $user->id,
                'title' => 'Aluguel',
                'amount' => 1500.00,
                'type' => TransactionTypeEnum::Debit,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 90])
            ->assertSuccessful();

        $transactions = Transaction::where('recurring_transaction_id', $recurring->id)
            ->orderBy('due_date')
            ->get();

        // Should generate transactions for current month + next 3 months (4 total in 90 days)
        $this->assertGreaterThanOrEqual(3, $transactions->count());
        $this->assertLessThanOrEqual(4, $transactions->count());

        // Check sequences are correct
        foreach ($transactions as $index => $transaction) {
            $this->assertEquals($index + 1, $transaction->sequence);
        }

        // Check if next_due_date was updated and generated_count incremented
        $recurring->refresh();
        $this->assertGreaterThanOrEqual(3, $recurring->generated_count);
        $this->assertGreaterThan(now(), $recurring->next_due_date);
    }

    public function test_generates_weekly_transactions(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->weekly()
            ->create([
                'user_id' => $user->id,
                'title' => 'Feira Semanal',
                'amount' => 200.00,
                'type' => TransactionTypeEnum::Debit,
                'start_date' => now()->startOfWeek(),
                'next_due_date' => now()->startOfWeek(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 30])
            ->assertSuccessful();

        // Should generate ~4-5 weekly transactions in 30 days
        $transactionCount = Transaction::where('recurring_transaction_id', $recurring->id)->count();
        $this->assertGreaterThanOrEqual(4, $transactionCount);
        $this->assertLessThanOrEqual(5, $transactionCount);
    }

    public function test_command_is_idempotent(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->create([
                'user_id' => $user->id,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        // Run command twice
        $this->artisan('app:generate-transactions', ['--days' => 30])->assertSuccessful();
        $firstCount = Transaction::count();

        $this->artisan('app:generate-transactions', ['--days' => 30])->assertSuccessful();
        $secondCount = Transaction::count();

        // Should not create duplicate transactions
        $this->assertEquals($firstCount, $secondCount);
    }

    public function test_stops_generation_when_end_date_reached(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->withEndDate()
            ->create([
                'user_id' => $user->id,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(2)->endOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 365])
            ->assertSuccessful();

        // Should only generate transactions until end_date (3 months including current)
        $transactionCount = Transaction::where('recurring_transaction_id', $recurring->id)->count();
        $this->assertEquals(3, $transactionCount);

        // Recurring should be deactivated
        $recurring->refresh();
        $this->assertFalse($recurring->active);
    }

    public function test_stops_generation_when_occurrences_limit_reached(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->withOccurrences(6)
            ->create([
                'user_id' => $user->id,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 365])
            ->assertSuccessful();

        // Should only generate 6 transactions
        $transactionCount = Transaction::where('recurring_transaction_id', $recurring->id)->count();
        $this->assertEquals(6, $transactionCount);

        // Recurring should be deactivated
        $recurring->refresh();
        $this->assertFalse($recurring->active);
    }

    public function test_recurring_transaction_has_many_transactions(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->create([
                'user_id' => $user->id,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 90])
            ->assertSuccessful();

        $this->assertGreaterThan(0, $recurring->transactions()->count());
    }

    public function test_transaction_belongs_to_recurring_transaction(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->create([
                'user_id' => $user->id,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 30])
            ->assertSuccessful();

        $transaction = Transaction::where('recurring_transaction_id', $recurring->id)->first();

        $this->assertNotNull($transaction->recurringTransaction);
        $this->assertEquals($recurring->id, $transaction->recurringTransaction->id);
    }

    public function test_generated_transactions_have_correct_attributes(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()
            ->monthly()
            ->create([
                'user_id' => $user->id,
                'title' => 'Aluguel',
                'description' => 'Pagamento mensal',
                'amount' => 1500.00,
                'type' => TransactionTypeEnum::Debit,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 30])
            ->assertSuccessful();

        $transaction = Transaction::where('recurring_transaction_id', $recurring->id)->first();

        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals('Aluguel', $transaction->title);
        $this->assertEquals('Pagamento mensal', $transaction->description);
        $this->assertEquals(1500.00, $transaction->amount);
        $this->assertEquals(TransactionTypeEnum::Debit, $transaction->type);
        $this->assertEquals(TransactionStatusEnum::Pending, $transaction->status);
        $this->assertEquals(1, $transaction->sequence);
    }

    public function test_only_processes_active_recurring_transactions(): void
    {
        $user = User::factory()->create();

        $active = RecurringTransaction::factory()
            ->monthly()
            ->create([
                'user_id' => $user->id,
                'active' => true,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $inactive = RecurringTransaction::factory()
            ->monthly()
            ->inactive()
            ->create([
                'user_id' => $user->id,
                'start_date' => now()->startOfMonth(),
                'next_due_date' => now()->startOfMonth(),
            ]);

        $this->artisan('app:generate-transactions', ['--days' => 30])
            ->assertSuccessful();

        // Only active recurring should generate transactions
        $this->assertGreaterThan(0, Transaction::where('recurring_transaction_id', $active->id)->count());
        $this->assertEquals(0, Transaction::where('recurring_transaction_id', $inactive->id)->count());
    }
}
