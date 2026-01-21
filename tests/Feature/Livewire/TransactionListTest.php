<?php

namespace Tests\Feature\Livewire;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\TransactionList;
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
            ->assertStatus(200)
            ->assertSee('Limpar filtros');
    }

    public function test_component_displays_transactions(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Transaction',
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->assertSee('Test Transaction');
    }

    public function test_search_filter_works(): void
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
            ->set('search', 'Grocery');

        // Verify that the filter returned only 1 transaction
        $this->assertCount(1, $component->get('transactions'));

        // Verify the filtered transaction is the correct one
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
            ->set('filterStatus', 'paid');

        // Verify that the filter returned only 1 transaction
        $this->assertCount(1, $component->get('transactions'));

        // Verify the filtered transaction is the correct one
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
            ->set('filterType', 'debit');

        // Verify that the filter returned only 1 transaction
        $this->assertCount(1, $component->get('transactions'));

        // Verify the filtered transaction is the correct one
        $this->assertEquals('Debit Transaction', $component->get('transactions')->first()->title);
        $this->assertEquals('debit', $component->get('transactions')->first()->type->value);
    }

    public function test_clear_filters_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->set('search', 'test')
            ->set('filterStatus', 'paid')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('filterStatus', null);
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

    public function test_pagination_resets_on_search(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class);

        $component->set('search', 'test');

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
            ->set('startDate', '2024-02-01')
            ->set('endDate', '2024-02-28');

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('February', $component->get('transactions')->first()->title);
    }

    public function test_tags_filter_works(): void
    {
        $user = User::factory()->create();
        $tag = \App\Models\Tag::factory()->create();

        $transaction1 = Transaction::factory()->for($user)->create(['title' => 'Tagged']);
        $transaction1->tags()->attach($tag);

        Transaction::factory()->for($user)->create(['title' => 'Not Tagged']);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('selectedTags', [$tag->id]);

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Tagged', $component->get('transactions')->first()->title);
    }

    public function test_recurring_filter_works(): void
    {
        $user = User::factory()->create();

        $recurring = \App\Models\RecurringTransaction::factory()->create(['user_id' => $user->id]);

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
            ->set('filterRecurrence', 'recurring');

        $this->assertCount(1, $component->get('transactions'));
        $this->assertEquals('Recurring', $component->get('transactions')->first()->title);
    }

    public function test_has_active_filters_returns_true_when_filters_set(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('search', 'test');

        $this->assertTrue($component->get('hasActiveFilters'));
    }

    public function test_has_active_filters_returns_false_when_no_filters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class);

        $this->assertFalse($component->get('hasActiveFilters'));
    }

    public function test_total_count_calculates_correctly(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->count(5)->for($user)->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class);

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

        $component = Livewire::test(TransactionList::class);

        $this->assertEquals(700.0, $component->get('totalAmount'));
    }

    public function test_close_modal_resets_editing_ids(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->set('editingTransactionId', 123)
            ->set('editingRecurringId', 456)
            ->call('closeModal')
            ->assertSet('editingTransactionId', null)
            ->assertSet('editingRecurringId', null);
    }

    public function test_open_edit_modal_sets_transaction_id(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('openEditModal', $transaction->id)
            ->assertSet('editingTransactionId', $transaction->id)
            ->assertSet('editingRecurringId', null);
    }

    public function test_refresh_list_clears_editing_and_resets_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->set('editingTransactionId', 123)
            ->dispatch('transaction-saved')
            ->assertSet('editingTransactionId', null)
            ->assertDispatched('close-modal')
            ->assertDispatched('notify');
    }

    public function test_refresh_list_recurring_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->set('editingRecurringId', 123)
            ->dispatch('recurring-saved')
            ->assertSet('editingRecurringId', null)
            ->assertDispatched('close-modal');
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
        $transaction = Transaction::factory()->for($otherUser)->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify');

        // Transaction should not be updated
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Pending->value,
        ]);
    }

    public function test_updated_selected_tags_resets_page(): void
    {
        $user = User::factory()->create();
        $tag = \App\Models\Tag::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('selectedTags', [$tag->id]);

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_updated_filter_status_resets_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('filterStatus', 'paid');

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_updated_filter_type_resets_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('filterType', 'debit');

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_updated_filter_recurrence_resets_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('filterRecurrence', 'recurring');

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_updated_start_date_resets_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('startDate', '2024-01-01');

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_updated_end_date_resets_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class)
            ->set('endDate', '2024-12-31');

        $this->assertEquals(1, $component->paginators['page']);
    }

    public function test_tags_are_loaded_on_mount(): void
    {
        $user = User::factory()->create();
        \App\Models\Tag::factory()->count(3)->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class);

        $this->assertCount(3, $component->get('tags'));
    }

    public function test_mount_dispatches_tags_loaded_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->assertDispatched('tags-loaded');
    }
}
