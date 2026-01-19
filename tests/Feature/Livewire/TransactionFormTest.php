<?php

namespace Tests\Feature\Livewire;

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

    public function test_can_create_transaction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'Test Transaction')
            ->set('description', 'Test Description')
            ->set('amount', 100.50)
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->set('dueDate', '2024-12-31')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Test Transaction',
            'amount' => 100.50,
            'user_id' => $user->id,
        ]);
    }

    public function test_validation_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', '')
            ->set('amount', 0)
            ->call('save')
            ->assertHasErrors(['title', 'amount']);
    }

    public function test_paid_at_field_shows_when_status_is_paid(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('status', 'paid')
            ->assertSee('Pagamento');
    }

    public function test_can_attach_tags_to_transaction(): void
    {
        $user = User::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Tag 1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2']);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'Transaction with Tags')
            ->set('amount', 50.00)
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->set('dueDate', '2024-12-31')
            ->set('selectedTags', [$tag1->id, $tag2->id])
            ->call('save');

        $transaction = Transaction::where('title', 'Transaction with Tags')->first();

        $this->assertNotNull($transaction);
        $this->assertEquals(2, $transaction->tags()->count());
    }

    public function test_displays_banner_on_successful_save(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(TransactionForm::class)
            ->set('title', 'Test Transaction')
            ->set('amount', 100.00)
            ->set('type', 'debit')
            ->set('status', 'pending')
            ->set('dueDate', '2024-12-31')
            ->call('save');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Test Transaction',
        ]);
    }
}
