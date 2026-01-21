<?php

namespace Tests\Feature\Controllers;

use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('transactions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_displays_transactions_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('transactions.index');
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->post(route('transactions.store'), [
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_store_validates_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'title' => '', // Invalid: required
            'amount' => 'invalid', // Invalid: not numeric
            'type' => 'invalid', // Invalid: not in enum
        ]);

        $response->assertSessionHasErrors(['title', 'amount', 'type', 'status', 'due_date']);
    }

    public function test_store_creates_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'user_id' => $user->id,
        ]);
    }

    public function test_store_creates_transaction_with_tags(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
            'tags' => [$tag->id],
        ]);

        $response->assertRedirect(route('transactions.index'));

        $transaction = Transaction::where('title', 'Test Transaction')->first();
        $this->assertTrue($transaction->tags->contains($tag));
    }

    public function test_store_redirects_with_success_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'title' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success', 'Transação criada com sucesso!');
    }

    public function test_show_requires_authentication(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->get(route('transactions.show', $transaction));

        $response->assertRedirect(route('login'));
    }

    public function test_show_displays_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create([
            'title' => 'Test Transaction',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.show', $transaction));

        $response->assertStatus(200);
        $response->assertViewIs('transactions.show');
        $response->assertSee('Test Transaction');
    }

    public function test_edit_requires_authentication(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->get(route('transactions.edit', $transaction));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_displays_form_with_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create([
            'title' => 'Test Transaction',
        ]);

        $response = $this->actingAs($user)->get(route('transactions.edit', $transaction));

        $response->assertStatus(200);
        $response->assertViewIs('transactions.edit');
        $response->assertViewHas('transaction');
        // Transaction data is rendered by Livewire component, not directly in view
    }

    public function test_edit_loads_tags(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();
        $tag = Tag::factory()->create(['name' => 'Test Tag']);

        $response = $this->actingAs($user)->get(route('transactions.edit', $transaction));

        $response->assertStatus(200);
        $response->assertViewHas('tags');
        // Tags are rendered by Livewire component, not directly in view
    }

    public function test_update_requires_authentication(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->put(route('transactions.update', $transaction), [
            'title' => 'Updated Transaction',
            'amount' => 200.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_update_validates_input(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'title' => '', // Invalid: required
            'amount' => 'invalid', // Invalid: not numeric
        ]);

        $response->assertSessionHasErrors(['title', 'amount']);
    }

    public function test_update_updates_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create([
            'title' => 'Original Title',
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'title' => 'Updated Title',
            'amount' => 200.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('transactions.index'));

        $transaction->refresh();
        $this->assertEquals('Updated Title', $transaction->title);
        $this->assertEquals(200.00, $transaction->amount);
    }

    public function test_update_redirects_with_success_message(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'title' => 'Updated Transaction',
            'amount' => 200.00,
            'type' => 'debit',
            'status' => 'pending',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success', 'Transação atualizada com sucesso!');
    }

    public function test_destroy_requires_authentication(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('login'));
    }

    public function test_destroy_deletes_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));

        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_destroy_redirects_with_success_message(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success', 'Transação excluída com sucesso!');
    }
}
