<?php

namespace Tests\Feature\Livewire;

use App\Enums\TransactionStatusEnum;
use App\Livewire\TransactionActions;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->assertStatus(200)
            ->assertSee('Excluir');
    }

    public function test_can_toggle_transaction_from_pending_to_paid(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->call('togglePaidStatus')
            ->assertDispatched('transaction-updated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);
    }

    public function test_can_toggle_transaction_from_paid_to_pending(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->call('togglePaidStatus')
            ->assertDispatched('transaction-updated');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);

        $transaction->refresh();
        $this->assertNull($transaction->paid_at);
    }

    public function test_confirm_delete_opens_modal(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->call('confirmDelete')
            ->assertSet('confirmingDelete', true);
    }

    public function test_can_delete_transaction(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->call('delete')
            ->assertDispatched('transaction-deleted');

        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_displays_correct_button_text_for_pending_transaction(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->assertSee('Marcar como Pago');
    }

    public function test_displays_correct_button_text_for_paid_transaction(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'status' => TransactionStatusEnum::Paid,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->assertSee('Marcar como Pendente');
    }

    public function test_modal_displays_transaction_details(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Transaction',
            'amount' => 150.00,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionActions::class, ['transaction' => $transaction])
            ->assertSee('Test Transaction')
            ->assertSee('150,00');
    }
}
