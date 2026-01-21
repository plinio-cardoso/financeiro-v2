<?php

namespace Tests\Feature\Mail;

use App\Mail\OverdueTransactions;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverdueTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_has_correct_subject(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new OverdueTransactions($transactions);

        $mailable->assertHasSubject('🚨 Atenção: Contas Vencidas - '.now()->format('d/m/Y'));
    }

    public function test_mail_uses_correct_view(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new OverdueTransactions($transactions);

        $mailable->assertHasView('emails.transactions.overdue');
    }

    public function test_mail_contains_transactions(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(3)->for($user)->create([
            'title' => 'Overdue Bill',
        ]);

        $mailable = new OverdueTransactions($transactions);

        $this->assertEquals(3, $mailable->transactions->count());
        $this->assertEquals('Overdue Bill', $mailable->transactions->first()->title);
    }

    public function test_mail_implements_should_queue(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new OverdueTransactions($transactions);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mailable);
    }

    public function test_mail_has_no_attachments(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new OverdueTransactions($transactions);

        $this->assertEmpty($mailable->attachments());
    }

    public function test_mail_renders_successfully(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create([
            'title' => 'Late Payment',
            'amount' => 500.00,
        ]);

        $mailable = new OverdueTransactions($transactions);

        $html = $mailable->render();

        $this->assertStringContainsString('Late Payment', $html);
    }
}
