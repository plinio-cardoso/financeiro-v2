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
            ->assertSee('Filtros');
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

        Livewire::test(TransactionList::class)
            ->set('search', 'Grocery')
            ->assertSee('Grocery Shopping')
            ->assertDontSee('Rent Payment');
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

        Livewire::test(TransactionList::class)
            ->set('filterStatus', 'paid')
            ->assertSee('Paid Transaction')
            ->assertDontSee('Pending Transaction');
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

        Livewire::test(TransactionList::class)
            ->set('filterType', 'debit')
            ->assertSee('Debit Transaction')
            ->assertDontSee('Credit Transaction');
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

    public function test_can_mark_pending_debit_as_paid(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Pending Debit',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify', message: 'Transação marcada como paga com sucesso!', type: 'success');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Paid->value,
        ]);

        $transaction->refresh();
        $this->assertNotNull($transaction->paid_at);
    }

    public function test_cannot_mark_credit_transaction_as_paid(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Credit Transaction',
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify', message: 'Apenas transações de débito podem ser marcadas como pagas.', type: 'error');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Pending->value,
        ]);
    }

    public function test_cannot_mark_already_paid_transaction_as_paid(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Already Paid',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify', message: 'Esta transação já está marcada como paga.', type: 'info');
    }

    public function test_cannot_mark_another_users_transaction_as_paid(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user2->id,
            'title' => 'Other User Transaction',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user1);

        Livewire::test(TransactionList::class)
            ->call('markAsPaid', $transaction->id)
            ->assertDispatched('notify', message: 'Transação não encontrada.', type: 'error');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Pending->value,
        ]);
    }

    public function test_mark_as_paid_button_only_appears_for_pending_debits(): void
    {
        $user = User::factory()->create();

        $pendingDebit = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Pending Debit',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $paidDebit = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Paid Debit',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
        ]);

        $pendingCredit = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Pending Credit',
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionList::class);

        // Should see mark as paid button for pending debit
        $component->assertSeeHtml('wire:click="markAsPaid('.$pendingDebit->id.')"');

        // Should not see mark as paid button for paid debit or credit
        $component->assertDontSeeHtml('wire:click="markAsPaid('.$paidDebit->id.')"');
        $component->assertDontSeeHtml('wire:click="markAsPaid('.$pendingCredit->id.')"');
    }
}
