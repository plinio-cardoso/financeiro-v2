<?php

namespace Tests\Feature;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_amount_shows_green_when_positive(): void
    {
        $user = User::factory()->create();

        // Create more credits than debits
        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 1000,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Livewire::actingAs($user)
            ->test('transaction-list')
            ->assertSee('Saldo Período')
            ->assertSeeHtml('text-emerald-600');
    }

    public function test_total_amount_shows_red_when_negative(): void
    {
        $user = User::factory()->create();

        // Create more debits than credits
        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 300,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 1000,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Livewire::actingAs($user)
            ->test('transaction-list')
            ->assertSee('Saldo Período')
            ->assertSeeHtml('text-rose-600');
    }

    public function test_total_amount_shows_gray_when_zero(): void
    {
        $user = User::factory()->create();

        // Create equal credits and debits
        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Livewire::actingAs($user)
            ->test('transaction-list')
            ->assertSee('Saldo Período')
            ->assertSeeHtml('text-gray-600');
    }

    public function test_total_amount_calculation_is_correct(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 2500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 1500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $component = Livewire::actingAs($user)->test('transaction-list');

        $this->assertEquals(1000, $component->totalAmount);
    }

    public function test_negative_total_amount_calculation_is_correct(): void
    {
        $user = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Credit,
            'amount' => 500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'type' => TransactionTypeEnum::Debit,
            'amount' => 1500,
            'status' => TransactionStatusEnum::Pending,
        ]);

        $component = Livewire::actingAs($user)->test('transaction-list');

        $this->assertEquals(-1000, $component->totalAmount);
    }
}
