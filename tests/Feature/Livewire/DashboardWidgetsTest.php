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
}
