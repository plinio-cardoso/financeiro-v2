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
            'description' => 'Original Description',
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $this->actingAs($user);

        Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id])
            ->set('description', 'Updated Description')
            ->set('type', 'credit')
            ->set('status', 'paid')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Updated Description',
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
            'description' => 'Test Description',
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Paid,
        ]);
        $transaction->tags()->attach($tag1);

        $this->actingAs($user);

        $component = Livewire::test(TransactionForm::class, ['transactionId' => $transaction->id]);

        $this->assertEquals('Test Description', $component->get('description'));
        $this->assertEquals('credit', $component->get('type'));
        $this->assertEquals('paid', $component->get('status'));
        $this->assertContains($tag1->id, $component->get('selectedTags'));
        $this->assertTrue($component->get('editing'));
    }
}
