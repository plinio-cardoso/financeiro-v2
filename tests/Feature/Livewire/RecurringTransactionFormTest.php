<?php

namespace Tests\Feature\Livewire;

use App\Enums\RecurringFrequencyEnum;
use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Livewire\RecurringTransactionForm;
use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class RecurringTransactionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RecurringTransactionForm::class)
            ->assertStatus(200);
    }

    public function test_can_create_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $startDate = now()->format('Y-m-d');

        Livewire::test(RecurringTransactionForm::class)
            ->set('title', 'Monthly Subscription')
            ->set('amount', '50.00')
            ->set('type', 'debit')
            ->set('frequency', 'monthly')
            ->set('interval', 1)
            ->set('startDate', $startDate)
            ->call('save')
            ->assertDispatched('recurring-saved');

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'title' => 'Monthly Subscription',
            'amount' => 50.00,
            'frequency' => 'monthly',
            'interval' => 1,
            'start_date' => $startDate,
        ]);
    }

    public function test_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(RecurringTransactionForm::class)
            ->set('title', '')
            ->set('amount', '')
            ->set('frequency', 'invalid')
            ->call('save')
            ->assertHasErrors(['title', 'amount', 'frequency']);
    }

    public function test_mounts_with_existing_data(): void
    {
        $user = User::factory()->create();
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Existing Rule',
            'amount' => 100.00,
            'frequency' => RecurringFrequencyEnum::Weekly,
            'interval' => 2,
            'start_date' => '2024-01-01',
        ]);

        $this->actingAs($user);

        Livewire::test(RecurringTransactionForm::class, ['recurringId' => $recurring->id])
            ->assertSet('title', 'Existing Rule')
            ->assertSet('amount', 100.00)
            ->assertSet('frequency', 'weekly')
            ->assertSet('interval', 2)
            ->assertSet('startDate', '2024-01-01')
            ->assertSet('editing', true);
    }

    public function test_can_update_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $this->actingAs($user);

        Livewire::test(RecurringTransactionForm::class, ['recurringId' => $recurring->id])
            ->set('title', 'New Title')
            ->call('save')
            ->assertDispatched('recurring-saved');

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'title' => 'New Title',
        ]);
    }

    public function test_can_delete_recurring_transaction(): void
    {
        $user = User::factory()->create();
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(RecurringTransactionForm::class, ['recurringId' => $recurring->id])
            ->set('deletionOption', 'only_recurrence')
            ->call('deleteRecurring')
            ->assertDispatched('recurring-saved');

        $this->assertSoftDeleted('recurring_transactions', [
            'id' => $recurring->id,
        ]);
    }

    public function test_generates_transactions_for_current_and_next_month_on_save(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // We use a known date to verify generation
        // If today is 2024-01-15, end of next month is 2024-02-29
        $startDate = now()->startOfMonth()->format('Y-m-d');

        Livewire::test(RecurringTransactionForm::class)
            ->set('title', 'Forecast Test')
            ->set('amount', '10.00')
            ->set('frequency', 'monthly')
            ->set('interval', 1)
            ->set('startDate', $startDate)
            ->call('save');

        // It should have generated at least 2 transactions (this month and next month)
        // since we started at the beginning of this month.
        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_recalculates_schedule_when_frequency_changes(): void
    {
        $user = User::factory()->create();
        // Create a monthly recurrence starting in the past
        $recurring = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'frequency' => RecurringFrequencyEnum::Monthly,
            'interval' => 1,
            'start_date' => now()->subMonths(2)->format('Y-m-d'),
            'next_due_date' => now()->addMonth()->format('Y-m-d'),
        ]);

        $this->actingAs($user);

        // Change to weekly starting from now
        $newStartDate = now()->format('Y-m-d');

        Livewire::test(RecurringTransactionForm::class, ['recurringId' => $recurring->id])
            ->set('frequency', 'weekly')
            ->set('startDate', $newStartDate)
            ->call('save');

        $recurring->refresh();

        // The next_due_date should have progressed because save() triggers generation
        // end of next month is the horizon.
        $this->assertTrue($recurring->next_due_date->gt(now()->addMonth()->endOfMonth()));

        // Also verify that a transaction was created for the new start date
        $this->assertDatabaseHas('transactions', [
            'recurring_transaction_id' => $recurring->id,
            'due_date' => $newStartDate,
        ]);
    }
}
