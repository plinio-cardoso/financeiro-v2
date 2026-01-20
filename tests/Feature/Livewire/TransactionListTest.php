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
}
