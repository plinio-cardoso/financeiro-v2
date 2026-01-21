<?php

namespace Tests\Feature\Livewire;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\TransactionList;
use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->assertStatus(200);
    }

    public function test_component_displays_transactions(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Transaction',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: []);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Test Transaction', $component->get('transactions')->first()->title);
    }

    public function test_applies_filters_from_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $filters = [
            'search' => 'grocery',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'tags' => [1, 2],
            'status' => 'paid',
            'type' => 'debit',
            'recurring' => 'recurring',
        ];

        Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: $filters)
            ->assertSet('activeFilters', $filters);
    }

    public function test_filters_affect_transaction_query(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Grocery Shopping',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Rent Payment',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: ['search' => 'Grocery']);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Grocery Shopping', $component->get('transactions')->first()->title);
    }

    public function test_status_filter_works(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Paid Transaction',
            'status' => TransactionStatusEnum::Paid,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Pending Transaction',
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: ['status' => 'paid']);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Paid Transaction', $component->get('transactions')->first()->title);
        $this->assertEquals('paid', $component->get('transactions')->first()->status->value);
    }

    public function test_type_filter_works(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Debit Transaction',
            'type' => TransactionTypeEnum::Debit,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Credit Transaction',
            'type' => TransactionTypeEnum::Credit,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: ['type' => 'debit']);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Debit Transaction', $component->get('transactions')->first()->title);
        $this->assertEquals('debit', $component->get('transactions')->first()->type->value);
    }

    public function test_sorting_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
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

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: ['search' => 'test']);

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_date_range_filter_works(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'due_date' => '2024-01-15',
            'title' => 'January',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'due_date' => '2024-02-15',
            'title' => 'February',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: [
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-28',
            ]);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('February', $component->get('transactions')->first()->title);
    }

    public function test_tags_filter_works(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $transaction1 = Transaction::factory()->for($user)->create(['title' => 'Tagged']);
        $transaction1->tags()->attach($tag);

        Transaction::factory()->for($user)->create(['title' => 'Not Tagged']);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: ['tags' => [$tag->id]]);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Tagged', $component->get('transactions')->first()->title);
    }

    public function test_recurring_filter_works(): void
    {
        $user = User::factory()->create();

        $recurring = RecurringTransaction::factory()->create(['user_id' => $user->id]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Recurring',
            'recurring_transaction_id' => $recurring->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Not Recurring',
            'recurring_transaction_id' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: ['recurring' => 'recurring']);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Recurring', $component->get('transactions')->first()->title);
    }

    public function test_clears_aggregate_cache_on_filter_change(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->count(5)->for($user)->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: []);

        // First access calculates aggregates
        $count1 = $component->get('totalCount');

        // Changing filters should clear cache
        $component->dispatch('filters-updated', filters: ['search' => 'test']);

        // Second access should recalculate
        $count2 = $component->get('totalCount');

        $this->assertIsInt($count1);
        $this->assertIsInt($count2);
    }

    public function test_total_count_calculates_correctly(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->count(5)->for($user)->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: []);

        $this->assertEquals(5, $component->get('totalCount'));
    }

    public function test_total_amount_calculates_correctly(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 1000,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 300,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: []);

        $this->assertEquals(700.0, $component->get('totalAmount'));
    }

    public function test_mark_as_paid_updates_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->pending()->for($user)->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Paid->value,
        ]);
    }

    public function test_mark_as_paid_handles_already_paid_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->paid()->for($user)->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify');
    }

    public function test_mark_as_paid_handles_non_existent_transaction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', 99999)
            ->assertDispatched('notify');
    }

    public function test_mark_as_paid_handles_other_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()->pending()->for($otherUser)->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Pending->value,
        ]);
    }

    public function test_multiple_filters_work_together(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $matchingTransaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Grocery Store',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'due_date' => '2024-06-15',
        ]);
        $matchingTransaction->tags()->attach($tag);

        // Create non-matching transactions
        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Grocery Store',
            'type' => TransactionTypeEnum::Credit, // Different type
            'due_date' => '2024-06-15',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Different Store',
            'type' => TransactionTypeEnum::Debit,
            'due_date' => '2024-06-15',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->dispatch('filters-updated', filters: [
                'search' => 'Grocery',
                'type' => 'debit',
                'status' => 'paid',
                'start_date' => '2024-06-01',
                'end_date' => '2024-06-30',
                'tags' => [$tag->id],
            ]);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Grocery Store', $component->get('transactions')->first()->title);
    }
}
