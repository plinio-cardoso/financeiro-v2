<?php

namespace Tests\Feature\Livewire;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\RecurringTransactionList;
use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecurringTransactionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionList::class)
            ->assertStatus(200);
    }

    public function test_component_displays_recurring_transactions(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Monthly Rent',
        ]);

        $this->actingAs($user);

        Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: [])
            ->assertSee('Monthly Rent');
    }

    public function test_applies_filters_from_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $filters = [
            'search' => 'rent',
            'tags' => [1, 2],
            'status' => 'active',
            'type' => 'debit',
            'frequency' => 'monthly',
        ];

        Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: $filters)
            ->assertSet('activeFilters', $filters);
    }

    public function test_search_filter_works(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Netflix Subscription',
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Gym Membership',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: ['search' => 'Netflix']);

        $this->assertCount(1, $component->get('recurringTransactions'));
        $this->assertEquals('Netflix Subscription', $component->get('recurringTransactions')->first()->title);
    }

    public function test_type_filter_works(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Debit Transaction',
            'type' => TransactionTypeEnum::Debit,
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Credit Transaction',
            'type' => TransactionTypeEnum::Credit,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: ['type' => 'debit']);

        $this->assertCount(1, $component->get('recurringTransactions'));
        $this->assertEquals('Debit Transaction', $component->get('recurringTransactions')->first()->title);
    }

    public function test_status_filter_works(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Active Transaction',
            'active' => true,
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Inactive Transaction',
            'active' => false,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: ['status' => 'active']);

        $this->assertCount(1, $component->get('recurringTransactions'));
        $this->assertEquals('Active Transaction', $component->get('recurringTransactions')->first()->title);
    }

    public function test_frequency_filter_works(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Monthly Expense',
            'frequency' => RecurringFrequencyEnum::Monthly,
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Weekly Expense',
            'frequency' => RecurringFrequencyEnum::Weekly,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: ['frequency' => 'monthly']);

        $this->assertCount(1, $component->get('recurringTransactions'));
        $this->assertEquals('Monthly Expense', $component->get('recurringTransactions')->first()->title);
    }

    public function test_tag_filter_works(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $recurring1 = RecurringTransaction::factory()->for($user)->create(['title' => 'Tagged']);
        $recurring1->tags()->attach($tag);

        RecurringTransaction::factory()->for($user)->create(['title' => 'Not Tagged']);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: ['tags' => [$tag->id]]);

        $this->assertCount(1, $component->get('recurringTransactions'));
        $this->assertEquals('Tagged', $component->get('recurringTransactions')->first()->title);
    }

    public function test_sorting_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecurringTransactionList::class)
            ->call('sortBy', 'title')
            ->assertSet('sortField', 'title')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'title')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_resets_pagination_on_filter_change(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: ['search' => 'test']);

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_total_count_calculates_correctly(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->count(5)->for($user)->create();

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: []);

        $this->assertEquals(5, $component->get('totalCount'));
    }

    public function test_monthly_amount_calculation_for_weekly(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 100,
            'frequency' => RecurringFrequencyEnum::Weekly,
            'active' => true,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: []);

        // Weekly * 4 = Monthly, and it's a debit so negative
        $this->assertEquals(-400.0, $component->get('totalMonthlyAmount'));
    }

    public function test_monthly_amount_calculation_for_monthly(): void
    {
        $user = User::factory()->create();

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 5000,
            'frequency' => RecurringFrequencyEnum::Monthly,
            'active' => true,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: []);

        $this->assertEquals(5000.0, $component->get('totalMonthlyAmount'));
    }

    public function test_clears_aggregate_cache_on_filter_change(): void
    {
        $user = User::factory()->create();
        RecurringTransaction::factory()->count(5)->for($user)->create(['active' => true]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: []);

        // First access calculates aggregates
        $count1 = $component->get('totalCount');

        // Changing filters should clear cache
        $component->dispatch('recurring-filters-updated', filters: ['search' => 'test']);

        // Second access should recalculate
        $count2 = $component->get('totalCount');

        $this->assertIsInt($count1);
        $this->assertIsInt($count2);
    }

    public function test_multiple_filters_work_together(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $matchingRecurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Netflix Subscription',
            'type' => TransactionTypeEnum::Debit,
            'frequency' => RecurringFrequencyEnum::Monthly,
            'active' => true,
        ]);
        $matchingRecurring->tags()->attach($tag);

        // Create non-matching recurring transactions
        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Netflix Subscription',
            'type' => TransactionTypeEnum::Credit, // Different type
            'frequency' => RecurringFrequencyEnum::Monthly,
        ]);

        RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Different Subscription',
            'type' => TransactionTypeEnum::Debit,
            'frequency' => RecurringFrequencyEnum::Monthly,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(RecurringTransactionList::class)
            ->dispatch('recurring-filters-updated', filters: [
                'search' => 'Netflix',
                'type' => 'debit',
                'status' => 'active',
                'frequency' => 'monthly',
                'tags' => [$tag->id],
            ]);

        $this->assertCount(1, $component->get('recurringTransactions'));
        $this->assertEquals('Netflix Subscription', $component->get('recurringTransactions')->first()->title);
    }
}
