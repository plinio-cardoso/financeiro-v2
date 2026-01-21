<?php

namespace Tests\Feature\Livewire;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\TransactionForm;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->assertStatus(200);
    }

    public function test_can_edit_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id])
            ->set('title', 'Updated Title')
            ->set('type', 'credit')
            ->set('status', 'paid')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'title' => 'Updated Title',
            'type' => 'credit',
            'status' => 'paid',
        ]);
    }

    public function test_validation_works(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id])
            ->set('type', 'invalid')
            ->set('status', 'invalid')
            ->call('save')
            ->assertHasErrors(['type', 'status']);
    }

    public function test_can_attach_tags_to_transaction(): void
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id])
            ->set('selectedTags', [$tag1->id, $tag2->id])
            ->call('save');

        $transaction->refresh();

        $this->assertEquals(2, $transaction->tags()->count());
        $this->assertTrue($transaction->tags->contains($tag1));
        $this->assertTrue($transaction->tags->contains($tag2));
    }

    public function test_mounts_with_transaction_data(): void
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Transaction',
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Paid,
        ]);
        $transaction->tags()->attach($tag1);

        $this->actingAs($user);

        $component = Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id]);

        $this->assertEquals('Test Transaction', $component->get('title'));
        $this->assertEquals('credit', $component->get('type'));
        $this->assertEquals('paid', $component->get('status'));
        $this->assertContains($tag1->id, $component->get('selectedTags'));
        $this->assertTrue($component->get('editing'));
    }

    public function test_can_create_simple_transaction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'New Transaction')
            ->set('amount', '100.00')
            ->set('dueDate', '2024-12-25')
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->call('save')
            ->assertDispatched('transaction-saved')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('transactions', [
            'title' => 'New Transaction',
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);
    }

    public function test_amount_formatting_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'Formatted Amount')
            ->set('amount', 'R$ 1.234,56')
            ->set('dueDate', '2024-12-25')
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Formatted Amount',
            'amount' => 1234.56,
        ]);
    }

    public function test_can_create_recurring_transaction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'Recurring Bill')
            ->set('amount', '100.00')
            ->set('dueDate', '2024-12-01')
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->set('isRecurring', true)
            ->set('frequency', 'monthly')
            ->set('interval', 1)
            ->set('startDate', '2024-12-01')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('recurring_transactions', [
            'title' => 'Recurring Bill',
            'frequency' => 'monthly',
        ]);
    }

    public function test_validation_requires_recurrence_fields_when_recurring(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'Recurring Bill')
            ->set('amount', '100.00')
            ->set('dueDate', '2024-12-25')
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->set('isRecurring', true)
            ->call('save')
            ->assertHasErrors(['frequency', 'interval', 'startDate']);
    }

    public function test_validation_messages_are_in_portuguese(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionForm::class)
            ->set('title', '')
            ->set('amount', '')
            ->set('dueDate', '')
            ->call('save')
            ->assertHasErrors(['title', 'amount', 'dueDate']);

        $errors = $component->instance()->getErrorBag();
        $this->assertStringContainsString('obrigatório', $errors->first('title'));
    }

    public function test_dispatches_validation_failed_on_errors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', '')
            ->call('save')
            ->assertDispatched('validation-failed');
    }

    public function test_mounts_with_recurring_transaction_data(): void
    {
        $user = User::factory()->create();

        $recurring = \App\Models\RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Monthly Bill',
            'frequency' => \App\Enums\RecurringFrequencyEnum::Monthly,
            'interval' => 2,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'occurrences' => 6,
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'recurring_transaction_id' => $recurring->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id]);

        $this->assertTrue($component->get('isRecurring'));
        $this->assertEquals('monthly', $component->get('frequency'));
        $this->assertEquals(2, $component->get('interval'));
        $this->assertEquals('2024-01-01', $component->get('startDate'));
        $this->assertEquals('2024-12-31', $component->get('endDate'));
        $this->assertEquals(6, $component->get('occurrences'));
    }

    public function test_mounts_with_default_dates_when_creating(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(TransactionForm::class);

        $this->assertEquals(now()->format('Y-m-d'), $component->get('dueDate'));
        $this->assertNull($component->get('startDate'));
        $this->assertFalse($component->get('editing'));
    }

    public function test_can_update_transaction_without_changing_recurring_status(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original',
            'recurring_transaction_id' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id])
            ->set('title', 'Updated')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'title' => 'Updated',
        ]);

        // Should not create a recurring transaction
        $this->assertDatabaseCount('recurring_transactions', 0);
    }

    public function test_regression_edit_transaction_value_formatting(): void
    {
        $user = User::factory()->create();
        // Create transaction with value 1234.56
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'amount' => 1234.56,
        ]);

        $this->actingAs($user);

        // Mount component - should format to 1.234,56 internally
        // Then save without changes
        Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id])
            ->call('save')
            ->assertDispatched('transaction-saved');

        // Verify value remains 1234.56 in DB, not 123456.00
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 1234.56,
        ]);
    }
}
