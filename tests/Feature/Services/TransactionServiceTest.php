<?php

namespace Tests\Feature\Services;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = app(TransactionService::class);
    }

    // ==================== Create ====================

    public function test_create_transaction_creates_record_in_database(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => '2024-12-25',
        ];

        $transaction = $this->transactionService->createTransaction($data);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertDatabaseHas('transactions', [
            'title' => 'Test Transaction',
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);
    }

    public function test_create_transaction_associates_tags(): void
    {
        $user = User::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'Transaction with Tags',
            'amount' => 100.00,
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => '2024-12-25',
            'tags' => $tags->pluck('id')->toArray(),
        ];

        $transaction = $this->transactionService->createTransaction($data);

        $this->assertCount(2, $transaction->tags);
        $this->assertTrue($transaction->tags->contains($tags->first()));
    }

    public function test_create_transaction_sets_user_id(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'User Transaction',
            'amount' => 50.00,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => '2024-12-25',
        ];

        $transaction = $this->transactionService->createTransaction($data);

        $this->assertEquals($user->id, $transaction->user_id);
    }

    // ==================== Update ====================

    public function test_update_transaction_updates_fields(): void
    {
        $transaction = Transaction::factory()->create([
            'title' => 'Old Title',
            'amount' => 100.00,
        ]);

        $data = [
            'title' => 'New Title',
            'amount' => 200.00,
        ];

        $updated = $this->transactionService->updateTransaction($transaction, $data);

        $this->assertEquals('New Title', $updated->title);
        $this->assertEquals('200.00', $updated->amount);
    }

    public function test_update_transaction_syncs_tags(): void
    {
        $transaction = Transaction::factory()->create();
        $oldTags = Tag::factory()->count(2)->create();
        $newTags = Tag::factory()->count(3)->create();

        $transaction->tags()->sync($oldTags->pluck('id'));
        $this->assertCount(2, $transaction->tags);

        $data = [
            'tags' => $newTags->pluck('id')->toArray(),
        ];

        $updated = $this->transactionService->updateTransaction($transaction, $data);

        $this->assertCount(3, $updated->tags);
        $this->assertFalse($updated->tags->contains($oldTags->first()));
        $this->assertTrue($updated->tags->contains($newTags->first()));
    }

    public function test_update_transaction_accepts_transaction_id(): void
    {
        $transaction = Transaction::factory()->create(['title' => 'Old Title']);

        $updated = $this->transactionService->updateTransaction(
            $transaction->id,
            ['title' => 'Updated Title']
        );

        $this->assertEquals('Updated Title', $updated->title);
    }

    // ==================== Delete ====================

    public function test_delete_transaction_removes_from_database(): void
    {
        $transaction = Transaction::factory()->create();

        $result = $this->transactionService->deleteTransaction($transaction);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_delete_transaction_removes_tag_associations(): void
    {
        $transaction = Transaction::factory()->create();
        $tags = Tag::factory()->count(2)->create();
        $transaction->tags()->sync($tags->pluck('id'));

        $this->assertDatabaseCount('transaction_tag', 2);

        $this->transactionService->deleteTransaction($transaction);

        $this->assertDatabaseCount('transaction_tag', 0);
    }

    public function test_delete_transaction_accepts_transaction_id(): void
    {
        $transaction = Transaction::factory()->create();

        $result = $this->transactionService->deleteTransaction($transaction->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    // ==================== Mark as Paid/Pending ====================

    public function test_mark_as_paid_sets_status_and_date(): void
    {
        $transaction = Transaction::factory()->pending()->create();

        $result = $this->transactionService->markAsPaid($transaction);

        $this->assertEquals(TransactionStatusEnum::Paid, $result->status);
        $this->assertNotNull($result->paid_at);
    }

    public function test_mark_as_pending_clears_paid_at(): void
    {
        $transaction = Transaction::factory()->paid()->create();

        $result = $this->transactionService->markAsPending($transaction);

        $this->assertEquals(TransactionStatusEnum::Pending, $result->status);
        $this->assertNull($result->paid_at);
    }

    // ==================== Calculations ====================

    public function test_calculate_monthly_totals_returns_correct_values(): void
    {
        $user = User::factory()->create();

        // Create debits for December 2024
        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'amount' => 100.00,
            'due_date' => '2024-12-10',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'amount' => 150.00,
            'due_date' => '2024-12-20',
        ]);

        $totals = $this->transactionService->calculateMonthlyTotals($user->id, 2024, 12);

        $this->assertEquals(250.00, $totals['total_due']);
        $this->assertEquals(100.00, $totals['total_paid']);
        $this->assertEquals(150.00, $totals['total_pending']);
    }

    public function test_calculate_monthly_totals_only_includes_debits(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100.00,
            'due_date' => '2024-12-10',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 200.00,
            'due_date' => '2024-12-15',
        ]);

        $totals = $this->transactionService->calculateMonthlyTotals($user->id, 2024, 12);

        $this->assertEquals(100.00, $totals['total_due']);
    }

    public function test_calculate_monthly_totals_separates_paid_and_pending(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->paid()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100.00,
            'due_date' => '2024-12-10',
        ]);

        Transaction::factory()->pending()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 200.00,
            'due_date' => '2024-12-20',
        ]);

        $totals = $this->transactionService->calculateMonthlyTotals($user->id, 2024, 12);

        $this->assertEquals(300.00, $totals['total_due']);
        $this->assertEquals(100.00, $totals['total_paid']);
        $this->assertEquals(200.00, $totals['total_pending']);
    }

    public function test_get_next_month_total_calculates_correctly(): void
    {
        $user = User::factory()->create();

        // Current month (December 2024) - should not be included
        Transaction::factory()->pending()->debit()->create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'due_date' => '2024-12-25',
        ]);

        // Next month (January 2025) - should be included
        Transaction::factory()->pending()->debit()->create([
            'user_id' => $user->id,
            'amount' => 150.00,
            'due_date' => '2025-01-15',
        ]);

        Transaction::factory()->pending()->debit()->create([
            'user_id' => $user->id,
            'amount' => 200.00,
            'due_date' => '2025-01-20',
        ]);

        $nextTotal = $this->transactionService->getNextMonthTotal($user->id, 2024, 12);

        $this->assertEquals(350.00, $nextTotal);
    }

    // ==================== Filtering ====================

    public function test_get_filtered_transactions_filters_by_search(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create(['title' => 'Electricity Bill']);
        Transaction::factory()->for($user)->create(['title' => 'Water Bill']);

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'search' => 'Electricity',
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Electricity Bill', $results->first()->title);
    }

    public function test_get_filtered_transactions_filters_by_date_range(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create(['due_date' => '2024-01-15']);
        Transaction::factory()->for($user)->create(['due_date' => '2024-02-15']);
        Transaction::factory()->for($user)->create(['due_date' => '2024-03-15']);

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-28',
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('2024-02-15', $results->first()->due_date->format('Y-m-d'));
    }

    public function test_get_filtered_transactions_filters_by_tags(): void
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Bills']);
        $tag2 = Tag::factory()->create(['name' => 'Shopping']);

        $transaction1 = Transaction::factory()->for($user)->create();
        $transaction1->tags()->attach($tag1);

        $transaction2 = Transaction::factory()->for($user)->create();
        $transaction2->tags()->attach($tag2);

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'tags' => [$tag1->id],
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->tags->contains($tag1));
    }

    public function test_get_filtered_transactions_filters_by_status(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->pending()->for($user)->create();
        Transaction::factory()->paid()->for($user)->create();

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'status' => TransactionStatusEnum::Pending,
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->isPending());
    }

    public function test_get_filtered_transactions_filters_by_type(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->debit()->for($user)->create();
        Transaction::factory()->credit()->for($user)->create();

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'type' => TransactionTypeEnum::Debit,
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->isDebit());
    }

    public function test_get_filtered_transactions_sorts_by_field(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create(['amount' => 300.00]);
        Transaction::factory()->for($user)->create(['amount' => 100.00]);
        Transaction::factory()->for($user)->create(['amount' => 200.00]);

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'sort_by' => 'amount',
            'sort_direction' => 'asc',
        ]);

        $results = $query->get();

        $this->assertEquals('100.00', $results->first()->amount);
        $this->assertEquals('300.00', $results->last()->amount);
    }

    public function test_get_filtered_transactions_eager_loads_tags(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();
        $tag = Tag::factory()->create();
        $transaction->tags()->attach($tag);

        $query = $this->transactionService->getFilteredTransactions($user->id, []);
        $result = $query->first();

        $this->assertTrue($result->relationLoaded('tags'));
        $this->assertCount(1, $result->tags);
    }

    public function test_get_filtered_transactions_filters_by_recurring(): void
    {
        $user = User::factory()->create();
        $recurring = \App\Models\RecurringTransaction::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->for($user)->create([
            'title' => 'Recurring',
            'recurring_transaction_id' => $recurring->id,
        ]);

        Transaction::factory()->for($user)->create([
            'title' => 'Not Recurring',
            'recurring_transaction_id' => null,
        ]);

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'recurring' => 'recurring',
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Recurring', $results->first()->title);
    }

    public function test_get_filtered_transactions_filters_by_not_recurring(): void
    {
        $user = User::factory()->create();
        $recurring = \App\Models\RecurringTransaction::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->for($user)->create([
            'recurring_transaction_id' => $recurring->id,
        ]);

        Transaction::factory()->for($user)->create([
            'title' => 'Not Recurring',
            'recurring_transaction_id' => null,
        ]);

        $query = $this->transactionService->getFilteredTransactions($user->id, [
            'recurring' => 'not_recurring',
        ]);

        $results = $query->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Not Recurring', $results->first()->title);
    }

    public function test_get_filtered_transactions_can_skip_select(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->for($user)->create(['amount' => 100]);

        $query = $this->transactionService->getFilteredTransactions($user->id, [], false);
        $result = $query->selectRaw('SUM(amount) as total')->first();

        $this->assertEquals(100, $result->total);
    }

    // ==================== Find Transaction ====================

    public function test_find_transaction_by_id_returns_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $found = $this->transactionService->findTransactionById($transaction->id, $user->id);

        $this->assertInstanceOf(Transaction::class, $found);
        $this->assertEquals($transaction->id, $found->id);
    }

    public function test_find_transaction_by_id_returns_null_for_wrong_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->for($otherUser)->create();

        $found = $this->transactionService->findTransactionById($transaction->id, $user->id);

        $this->assertNull($found);
    }

    public function test_find_transaction_by_id_returns_null_for_non_existent(): void
    {
        $user = User::factory()->create();

        $found = $this->transactionService->findTransactionById(99999, $user->id);

        $this->assertNull($found);
    }

    // ==================== Pending/Overdue/Due Today ====================

    public function test_get_pending_transactions_returns_only_pending(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->pending()->for($user)->create(['title' => 'Pending']);
        Transaction::factory()->paid()->for($user)->create(['title' => 'Paid']);

        $pending = $this->transactionService->getPendingTransactions($user->id);

        $this->assertCount(1, $pending);
        $this->assertEquals('Pending', $pending->first()->title);
    }

    public function test_get_pending_transactions_eager_loads_tags(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->pending()->for($user)->create();
        $tag = Tag::factory()->create();
        $transaction->tags()->attach($tag);

        $pending = $this->transactionService->getPendingTransactions($user->id);

        $this->assertTrue($pending->first()->relationLoaded('tags'));
    }

    public function test_get_overdue_transactions_returns_past_due_pending_only(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->pending()->for($user)->create([
            'title' => 'Overdue',
            'due_date' => now()->subDay(),
        ]);

        Transaction::factory()->pending()->for($user)->create([
            'title' => 'Future',
            'due_date' => now()->addDay(),
        ]);

        Transaction::factory()->paid()->for($user)->create([
            'title' => 'Paid Overdue',
            'due_date' => now()->subDay(),
        ]);

        $overdue = $this->transactionService->getOverdueTransactions($user->id);

        $this->assertCount(1, $overdue);
        $this->assertEquals('Overdue', $overdue->first()->title);
    }

    public function test_get_transactions_due_today_returns_only_today_pending(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->pending()->for($user)->create([
            'title' => 'Due Today',
            'due_date' => today(),
        ]);

        Transaction::factory()->pending()->for($user)->create([
            'title' => 'Due Tomorrow',
            'due_date' => today()->addDay(),
        ]);

        Transaction::factory()->paid()->for($user)->create([
            'title' => 'Paid Today',
            'due_date' => today(),
        ]);

        $dueToday = $this->transactionService->getTransactionsDueToday($user->id);

        $this->assertCount(1, $dueToday);
        $this->assertEquals('Due Today', $dueToday->first()->title);
    }

    // ==================== Create Recurring Transaction ====================

    public function test_create_recurring_transaction_creates_record(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'Monthly Bill',
            'amount' => 100.00,
            'type' => TransactionTypeEnum::Debit,
            'frequency' => \App\Enums\RecurringFrequencyEnum::Monthly,
            'interval' => 1,
            'start_date' => now()->addDay()->format('Y-m-d'),
        ];

        $recurring = $this->transactionService->createRecurringTransaction($data);

        $this->assertInstanceOf(\App\Models\RecurringTransaction::class, $recurring);
        $this->assertDatabaseHas('recurring_transactions', [
            'title' => 'Monthly Bill',
            'user_id' => $user->id,
        ]);
    }

    public function test_create_recurring_transaction_sets_defaults(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'Bill',
            'amount' => 50,
            'frequency' => \App\Enums\RecurringFrequencyEnum::Weekly,
            'start_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $recurring = $this->transactionService->createRecurringTransaction($data);

        $this->assertEquals(TransactionTypeEnum::Debit, $recurring->type);
        $this->assertEquals(1, $recurring->interval);
        $this->assertTrue($recurring->active);
    }

    public function test_create_recurring_transaction_associates_tags(): void
    {
        $user = User::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $data = [
            'user_id' => $user->id,
            'title' => 'Tagged Recurring',
            'amount' => 100,
            'frequency' => \App\Enums\RecurringFrequencyEnum::Monthly,
            'start_date' => now()->addMonth()->format('Y-m-d'),
            'tags' => $tags->pluck('id')->toArray(),
        ];

        $recurring = $this->transactionService->createRecurringTransaction($data);

        $this->assertCount(2, $recurring->tags);
    }
}
