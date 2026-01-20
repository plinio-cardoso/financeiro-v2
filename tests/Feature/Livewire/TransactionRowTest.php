<?php

namespace Tests\Feature\Livewire;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\TransactionRow;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionRowTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Transaction',
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->assertStatus(200)
            ->assertSee('Test Transaction');
    }

    public function test_can_update_title(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('updateField', 'title', 'Updated Title')
            ->assertDispatched('notify', message: 'Título atualizado com sucesso!', type: 'success')
            ->assertDispatched('transaction-updated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_update_amount(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('updateField', 'amount', '250.50')
            ->assertDispatched('notify', message: 'Valor atualizado com sucesso!', type: 'success')
            ->assertDispatched('transaction-updated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 250.50,
        ]);
    }

    public function test_can_update_due_date(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'due_date' => '2024-01-01',
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('updateField', 'due_date', '2024-12-31')
            ->assertDispatched('notify', message: 'Data de vencimento atualizado com sucesso!', type: 'success')
            ->assertDispatched('transaction-updated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'due_date' => '2024-12-31',
        ]);
    }

    public function test_cannot_update_invalid_field(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('updateField', 'invalid_field', 'value')
            ->assertDispatched('notify', message: 'Campo inválido.', type: 'error');
    }

    public function test_validates_required_title(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original',
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('updateField', 'title', '')
            ->assertDispatched('notify', type: 'error');
    }

    public function test_validates_minimum_amount(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('updateField', 'amount', '0')
            ->assertDispatched('notify', type: 'error');
    }

    public function test_can_mark_pending_debit_as_paid(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('markAsPaid')
            ->assertDispatched('notify', message: 'Transação marcada como paga com sucesso!', type: 'success')
            ->assertDispatched('transaction-updated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => TransactionStatusEnum::Paid->value,
        ]);

        $transaction->refresh();
        $this->assertNotNull($transaction->paid_at);
    }

    public function test_cannot_mark_already_paid_as_paid(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::Paid,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('markAsPaid')
            ->assertDispatched('notify', message: 'Esta transação já está marcada como paga.', type: 'info');
    }

    public function test_edit_dispatches_open_modal_event(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->call('edit')
            ->assertDispatched('open-edit-modal', transactionId: $transaction->id);
    }

    public function test_displays_recurring_icon_for_recurring_transactions(): void
    {
        $user = User::factory()->create();

        // Create a recurring transaction first
        $recurringTransaction = \App\Models\RecurringTransaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'recurring_transaction_id' => $recurringTransaction->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->assertSeeHtml('Transação recorrente');
    }

    public function test_displays_tags(): void
    {
        $user = User::factory()->create();

        // Create tag without user_id since Tag model doesn't have it
        $tag = \App\Models\Tag::factory()->create(['name' => 'Test Tag']);

        $transaction = Transaction::factory()
            ->hasAttached($tag)
            ->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionRow::class, ['transaction' => $transaction])
            ->assertSee('Test Tag');
    }
}
