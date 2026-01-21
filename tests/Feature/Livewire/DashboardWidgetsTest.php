<?php

namespace Tests\Feature\Livewire;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\DashboardWidgets;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DashboardWidgets::class)
            ->assertStatus(200);
    }

    public function test_upcoming_expenses_grouped_returns_grouped_data(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create([
            'due_date' => today(),
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $component->call('loadUpcomingExpenses');

        $grouped = $component->get('upcomingExpensesGrouped');

        $this->assertIsArray($grouped);
        $this->assertNotEmpty($grouped);
    }

    public function test_upcoming_expenses_grouped_returns_empty_array_when_not_loaded(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $grouped = $component->get('upcomingExpensesGrouped');

        $this->assertIsArray($grouped);
        $this->assertEmpty($grouped);
    }

    public function test_monthly_expense_total_returns_correct_sum(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        Transaction::factory()->count(2)->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'amount' => 50,
            'due_date' => now(),
        ])->each(fn ($t) => $t->tags()->attach($tag));

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $component->call('loadExpensesByTag');

        $total = $component->get('monthlyExpenseTotal');

        $this->assertEquals(100.0, $total);
    }

    public function test_monthly_expense_total_returns_zero_when_not_loaded(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $total = $component->get('monthlyExpenseTotal');

        $this->assertEquals(0.0, $total);
    }

    public function test_load_upcoming_expenses_populates_data(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create([
            'due_date' => today(),
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $this->assertEmpty($component->get('upcomingExpenses'));
        $this->assertFalse($component->get('loadedUpcoming'));

        $component->call('loadUpcomingExpenses');

        $this->assertNotEmpty($component->get('upcomingExpenses'));
        $this->assertTrue($component->get('loadedUpcoming'));
    }

    public function test_load_expenses_by_tag_populates_data(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100,
            'due_date' => now(),
        ])->tags()->attach($tag);

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $this->assertEmpty($component->get('expensesByTag'));
        $this->assertFalse($component->get('loadedByTag'));

        $component->call('loadExpensesByTag');

        $this->assertNotEmpty($component->get('expensesByTag'));
        $this->assertTrue($component->get('loadedByTag'));
    }

    public function test_load_recent_activity_populates_data(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->count(3)->for($user)->create([
            'created_at' => now()->subDay(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $this->assertEmpty($component->get('recentActivity'));
        $this->assertFalse($component->get('loadedRecent'));

        $component->call('loadRecentActivity');

        $this->assertNotEmpty($component->get('recentActivity'));
        $this->assertTrue($component->get('loadedRecent'));
    }

    public function test_load_recent_activity_only_loads_once(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->count(2)->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $component->call('loadRecentActivity');
        $firstCount = $component->get('recentActivity')->count();

        // Create more transactions
        Transaction::factory()->for($user)->create();

        // Call again - should not reload
        $component->call('loadRecentActivity');
        $secondCount = $component->get('recentActivity')->count();

        $this->assertEquals($firstCount, $secondCount);
    }

    public function test_load_monthly_comparison_populates_data(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100,
            'due_date' => now(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $this->assertEmpty($component->get('monthlyComparison'));
        $this->assertFalse($component->get('loadedComparison'));

        $component->call('loadMonthlyComparison');

        $this->assertNotEmpty($component->get('monthlyComparison'));
        $this->assertTrue($component->get('loadedComparison'));
    }

    public function test_load_monthly_comparison_dispatches_event(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DashboardWidgets::class)
            ->call('loadMonthlyComparison')
            ->assertDispatched('monthlyComparisonLoaded');
    }

    public function test_load_expenses_by_tag_dispatches_event(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DashboardWidgets::class)
            ->call('loadExpensesByTag')
            ->assertDispatched('expensesByTagLoaded');
    }

    public function test_mark_as_paid_updates_transaction(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->pending()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
        ]);

        Livewire::actingAs($user)
            ->test(DashboardWidgets::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify')
            ->assertDispatched('transactionUpdated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Paid->value,
        ]);
    }

    public function test_mark_as_paid_handles_non_existent_transaction(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(DashboardWidgets::class)
            ->call('markAsPaid', 99999);

        // Should not throw error, just silently handle
    }

    public function test_mark_as_paid_handles_exception(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Transaction belongs to other user
        $transaction = Transaction::factory()->pending()->for($otherUser)->create();

        Livewire::actingAs($user)
            ->test(DashboardWidgets::class)
            ->call('markAsPaid', $transaction->id);

        // Transaction should not be updated
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Paid->value,
        ]);
    }

    public function test_component_mounts_with_empty_collections(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $this->assertEmpty($component->get('recentActivity'));
        $this->assertEmpty($component->get('upcomingExpenses'));
        $this->assertEmpty($component->get('expensesByTag'));
        $this->assertEmpty($component->get('monthlyComparison'));
    }

    public function test_load_upcoming_expenses_only_loads_once(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->count(2)->for($user)->create([
            'due_date' => today()->addDay(),
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $component = Livewire::actingAs($user)
            ->test(DashboardWidgets::class);

        $component->call('loadUpcomingExpenses');
        $firstCount = $component->get('upcomingExpenses')->count();

        // Create more transactions
        Transaction::factory()->for($user)->create([
            'due_date' => today()->addDay(),
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        // Call again - should not reload
        $component->call('loadUpcomingExpenses');
        $secondCount = $component->get('upcomingExpenses')->count();

        $this->assertEquals($firstCount, $secondCount);
    }
}
