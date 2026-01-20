<?php

namespace Tests\Feature\Models;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    // ==================== Relationships ====================

    public function test_transaction_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($user->id, $transaction->user_id);
    }

    public function test_transaction_has_many_tags(): void
    {
        $transaction = Transaction::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $transaction->tags()->attach($tags->pluck('id'));

        $this->assertCount(3, $transaction->tags);
        $this->assertInstanceOf(Tag::class, $transaction->tags->first());
    }

    // ==================== Casts ====================

    public function test_amount_is_cast_to_decimal(): void
    {
        $transaction = Transaction::factory()->create(['amount' => 100.50]);

        $this->assertIsString($transaction->amount);
        $this->assertEquals('100.50', $transaction->amount);
    }

    public function test_due_date_is_cast_to_date(): void
    {
        $transaction = Transaction::factory()->create(['due_date' => '2024-12-25']);

        $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->due_date);
        $this->assertEquals('2024-12-25', $transaction->due_date->format('Y-m-d'));
    }

    public function test_paid_at_is_cast_to_datetime(): void
    {
        $transaction = Transaction::factory()->create(['paid_at' => '2024-12-25 10:30:00']);

        $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->paid_at);
        $this->assertEquals('2024-12-25 10:30:00', $transaction->paid_at->format('Y-m-d H:i:s'));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $transaction = Transaction::factory()->create(['status' => TransactionStatusEnum::Pending]);

        $this->assertInstanceOf(TransactionStatusEnum::class, $transaction->status);
        $this->assertEquals(TransactionStatusEnum::Pending, $transaction->status);
    }

    public function test_type_is_cast_to_enum(): void
    {
        $transaction = Transaction::factory()->create(['type' => TransactionTypeEnum::Debit]);

        $this->assertInstanceOf(TransactionTypeEnum::class, $transaction->type);
        $this->assertEquals(TransactionTypeEnum::Debit, $transaction->type);
    }

    // ==================== Scopes ====================

    public function test_pending_scope_returns_only_pending_transactions(): void
    {
        Transaction::factory()->pending()->create();
        Transaction::factory()->paid()->create();

        $pending = Transaction::pending()->get();

        $this->assertCount(1, $pending);
        $this->assertTrue($pending->first()->isPending());
    }

    public function test_debits_scope_returns_only_debits(): void
    {
        Transaction::factory()->debit()->create();
        Transaction::factory()->credit()->create();

        $debits = Transaction::debits()->get();

        $this->assertCount(1, $debits);
        $this->assertTrue($debits->first()->isDebit());
    }

    public function test_due_today_scope_returns_transactions_due_today(): void
    {
        Transaction::factory()->dueToday()->create();
        Transaction::factory()->create(['due_date' => now()->addDays(5)]);

        $dueToday = Transaction::dueToday()->get();

        $this->assertCount(1, $dueToday);
        $this->assertEquals(today()->toDateString(), $dueToday->first()->due_date->toDateString());
    }

    public function test_overdue_scope_returns_overdue_pending_transactions(): void
    {
        // Overdue and pending - should be included
        Transaction::factory()->overdue()->create();

        // Overdue but paid - should NOT be included
        Transaction::factory()->create([
            'due_date' => now()->subDays(5),
            'status' => TransactionStatusEnum::Paid,
        ]);

        // Not overdue - should NOT be included
        Transaction::factory()->pending()->create(['due_date' => now()->addDays(5)]);

        $overdue = Transaction::overdue()->get();

        $this->assertCount(1, $overdue);
        $this->assertTrue($overdue->first()->isOverdue());
    }

    // ==================== Actions (TransactionActionTrait) ====================

    public function test_mark_as_paid_sets_status_and_paid_at(): void
    {
        $transaction = Transaction::factory()->pending()->create();

        $this->assertNull($transaction->paid_at);
        $this->assertEquals(TransactionStatusEnum::Pending, $transaction->status);

        $transaction->markAsPaid();
        $transaction->refresh();

        $this->assertEquals(TransactionStatusEnum::Paid, $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $transaction->paid_at);
    }

    public function test_mark_as_pending_clears_paid_at(): void
    {
        $transaction = Transaction::factory()->paid()->create();

        $this->assertNotNull($transaction->paid_at);
        $this->assertEquals(TransactionStatusEnum::Paid, $transaction->status);

        $transaction->markAsPending();
        $transaction->refresh();

        $this->assertEquals(TransactionStatusEnum::Pending, $transaction->status);
        $this->assertNull($transaction->paid_at);
    }

    // ==================== Accessors (TransactionAccessorTrait) ====================

    public function test_get_formatted_amount_returns_brazilian_format(): void
    {
        $transaction = Transaction::factory()->create(['amount' => 1234.56]);

        $this->assertEquals('R$ 1.234,56', $transaction->getFormattedAmount());
    }

    public function test_get_formatted_due_date_returns_dd_mm_yyyy(): void
    {
        $transaction = Transaction::factory()->create(['due_date' => '2024-12-25']);

        $this->assertEquals('25/12/2024', $transaction->getFormattedDueDate());
    }

    public function test_is_pending_returns_true_when_pending(): void
    {
        $pending = Transaction::factory()->pending()->create();
        $paid = Transaction::factory()->paid()->create();

        $this->assertTrue($pending->isPending());
        $this->assertFalse($paid->isPending());
    }

    public function test_is_paid_returns_true_when_paid(): void
    {
        $paid = Transaction::factory()->paid()->create();
        $pending = Transaction::factory()->pending()->create();

        $this->assertTrue($paid->isPaid());
        $this->assertFalse($pending->isPaid());
    }

    public function test_is_overdue_returns_true_when_past_due_and_pending(): void
    {
        $overdue = Transaction::factory()->overdue()->create();
        $notOverdue = Transaction::factory()->pending()->create(['due_date' => now()->addDays(5)]);
        $paidButPastDue = Transaction::factory()->paid()->create(['due_date' => now()->subDays(5)]);

        $this->assertTrue($overdue->isOverdue());
        $this->assertFalse($notOverdue->isOverdue());
        $this->assertFalse($paidButPastDue->isOverdue());
    }

    public function test_get_days_until_due_returns_correct_count(): void
    {
        $now = now()->startOfDay();
        \Illuminate\Support\Carbon::setTestNow($now);

        $future = Transaction::factory()->create(['due_date' => $now->copy()->addDays(5)]);
        $past = Transaction::factory()->create(['due_date' => $now->copy()->subDays(3)]);

        $this->assertEquals(5, $future->getDaysUntilDue());
        $this->assertEquals(-3, $past->getDaysUntilDue());

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_is_debit_returns_true_for_debit_type(): void
    {
        $debit = Transaction::factory()->debit()->create();
        $credit = Transaction::factory()->credit()->create();

        $this->assertTrue($debit->isDebit());
        $this->assertFalse($credit->isDebit());
    }

    public function test_is_credit_returns_true_for_credit_type(): void
    {
        $credit = Transaction::factory()->credit()->create();
        $debit = Transaction::factory()->debit()->create();

        $this->assertTrue($credit->isCredit());
        $this->assertFalse($debit->isCredit());
    }

    public function test_get_type_color_class_returns_correct_colors(): void
    {
        $debit = Transaction::factory()->debit()->create();
        $credit = Transaction::factory()->credit()->create();

        $this->assertEquals('text-rose-500', $debit->getTypeColorClass());
        $this->assertEquals('text-emerald-500', $credit->getTypeColorClass());
    }

    public function test_get_sign_prefix_returns_correct_signs(): void
    {
        $debit = Transaction::factory()->debit()->create();
        $credit = Transaction::factory()->credit()->create();

        $this->assertEquals('-', $debit->getSignPrefix());
        $this->assertEquals('+', $credit->getSignPrefix());
    }

    public function test_get_amount_color_class_returns_correct_classes(): void
    {
        $debit = Transaction::factory()->debit()->create();
        $credit = Transaction::factory()->credit()->create();

        $this->assertStringContainsString('text-gray-900', $debit->getAmountColorClass());
        $this->assertEquals('text-emerald-500', $credit->getAmountColorClass());
    }
}
