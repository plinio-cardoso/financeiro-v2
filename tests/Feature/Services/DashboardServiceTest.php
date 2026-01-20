<?php

namespace Tests\Feature\Services;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = app(DashboardService::class);
    }

    public function test_get_current_month_stats_returns_correct_totals(): void
    {
        $user = User::factory()->create();

        // Create debits for current month
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'amount' => 100.00,
            'due_date' => now(),
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'amount' => 200.00,
            'due_date' => now(),
        ]);

        $stats = $this->dashboardService->getCurrentMonthStats($user->id);

        $this->assertEquals(300.00, $stats['total_due']);
        $this->assertEquals(200.00, $stats['total_paid']);
        $this->assertEquals(100.00, $stats['total_pending']);
        $this->assertEquals(2, $stats['transactions_count']);
    }

    public function test_get_current_month_stats_only_includes_debits(): void
    {
        $user = User::factory()->create();

        // Create debit
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100.00,
            'due_date' => now(),
        ]);

        // Create credit (should be excluded)
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Credit,
            'amount' => 500.00,
            'due_date' => now(),
        ]);

        $stats = $this->dashboardService->getCurrentMonthStats($user->id);

        $this->assertEquals(100.00, $stats['total_due']);
        $this->assertEquals(1, $stats['transactions_count']);
    }

    public function test_get_current_month_stats_includes_overdue_count(): void
    {
        $user = User::factory()->create();

        // Create overdue transaction
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        // Create future transaction (not overdue)
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->addDay(),
        ]);

        $stats = $this->dashboardService->getCurrentMonthStats($user->id);

        $this->assertEquals(1, $stats['overdue_count']);
    }

    public function test_get_current_month_stats_calculates_next_month_total(): void
    {
        $user = User::factory()->create();

        // Current month debit
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'amount' => 100.00,
            'due_date' => now(),
        ]);

        // Next month debit (pending)
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'amount' => 300.00,
            'due_date' => now()->addMonth(),
        ]);

        $stats = $this->dashboardService->getCurrentMonthStats($user->id);

        $this->assertEquals(300.00, $stats['next_month_total']);
    }

    public function test_get_monthly_transactions_returns_correct_month(): void
    {
        $user = User::factory()->create();

        // Current month
        $currentTransaction = Transaction::factory()->for($user)->create([
            'due_date' => now()->startOfMonth(),
        ]);

        // Previous month (should be excluded)
        Transaction::factory()->for($user)->create([
            'due_date' => now()->subMonth(),
        ]);

        $transactions = $this->dashboardService->getMonthlyTransactions($user->id);

        $this->assertCount(1, $transactions);
        $this->assertEquals($currentTransaction->id, $transactions->first()->id);
    }

    public function test_get_monthly_transactions_defaults_to_current_month(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create([
            'due_date' => now(),
        ]);

        $transactions = $this->dashboardService->getMonthlyTransactions($user->id);

        $this->assertCount(1, $transactions);
    }

    public function test_get_monthly_transactions_accepts_specific_year_and_month(): void
    {
        $user = User::factory()->create();

        // January 2024
        $januaryTransaction = Transaction::factory()->for($user)->create([
            'due_date' => '2024-01-15',
        ]);

        // February 2024
        Transaction::factory()->for($user)->create([
            'due_date' => '2024-02-15',
        ]);

        $transactions = $this->dashboardService->getMonthlyTransactions($user->id, 2024, 1);

        $this->assertCount(1, $transactions);
        $this->assertEquals($januaryTransaction->id, $transactions->first()->id);
    }

    public function test_get_monthly_transactions_eager_loads_tags(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->for($user)->create([
            'due_date' => now(),
        ]);

        $transactions = $this->dashboardService->getMonthlyTransactions($user->id);

        $this->assertTrue($transactions->first()->relationLoaded('tags'));
    }

    public function test_get_monthly_transactions_orders_by_due_date(): void
    {
        $user = User::factory()->create();

        $transaction1 = Transaction::factory()->for($user)->create([
            'due_date' => now()->addDays(10),
        ]);

        $transaction2 = Transaction::factory()->for($user)->create([
            'due_date' => now()->addDays(5),
        ]);

        $transactions = $this->dashboardService->getMonthlyTransactions($user->id);

        $this->assertEquals($transaction2->id, $transactions->first()->id);
        $this->assertEquals($transaction1->id, $transactions->last()->id);
    }

    public function test_get_upcoming_expenses_grouped_by_day_groups_correctly(): void
    {
        $user = User::factory()->create();

        // Create transactions for today
        Transaction::factory()->for($user)->create([
            'due_date' => today(),
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        // Create transaction for tomorrow
        Transaction::factory()->for($user)->create([
            'due_date' => today()->addDay(),
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $grouped = $this->dashboardService->getUpcomingExpensesGroupedByDay($user->id);

        // Should have 2 groups (today and tomorrow)
        $this->assertCount(2, $grouped);

        // Each group should have 1 transaction
        foreach ($grouped as $dayExpenses) {
            $this->assertCount(1, $dayExpenses);
        }
    }

    public function test_get_upcoming_expenses_grouped_by_day_returns_empty_when_no_expenses(): void
    {
        $user = User::factory()->create();

        $grouped = $this->dashboardService->getUpcomingExpensesGroupedByDay($user->id);

        $this->assertIsArray($grouped);
        $this->assertEmpty($grouped);
    }

    public function test_get_current_month_expense_total_sums_correctly(): void
    {
        $user = User::factory()->create();
        $tag = \App\Models\Tag::factory()->create();

        // Create transactions for the current month
        Transaction::factory()->count(3)->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100,
            'due_date' => now(),
        ])->each(fn ($t) => $t->tags()->attach($tag));

        $total = $this->dashboardService->getCurrentMonthExpenseTotal($user->id);

        $this->assertEquals(300.0, $total);
    }

    public function test_get_current_month_expense_total_returns_zero_when_no_expenses(): void
    {
        $user = User::factory()->create();

        $total = $this->dashboardService->getCurrentMonthExpenseTotal($user->id);

        $this->assertEquals(0.0, $total);
    }
}
