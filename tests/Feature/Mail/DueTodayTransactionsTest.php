<?php

namespace Tests\Feature\Mail;

use App\Mail\DueTodayTransactions;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueTodayTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_has_correct_subject(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new DueTodayTransactions($transactions);

        $mailable->assertHasSubject('Contas Vencendo Hoje - '.now()->format('d/m/Y'));
    }

    public function test_mail_uses_correct_view(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new DueTodayTransactions($transactions);

        $mailable->assertSeeInHtml('Contas Vencendo Hoje');
    }

    public function test_mail_contains_transactions(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create([
            'title' => 'Test Transaction',
        ]);

        $mailable = new DueTodayTransactions($transactions);

        $this->assertEquals(2, $mailable->transactions->count());
        $this->assertEquals('Test Transaction', $mailable->transactions->first()->title);
    }

    public function test_mail_implements_should_queue(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new DueTodayTransactions($transactions);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mailable);
    }

    public function test_mail_has_no_attachments(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create();

        $mailable = new DueTodayTransactions($transactions);

        $this->assertEmpty($mailable->attachments());
    }

    public function test_mail_renders_successfully(): void
    {
        $user = User::factory()->create();
        $transactions = Transaction::factory()->count(2)->for($user)->create([
            'title' => 'Rent Payment',
            'amount' => 1000.00,
        ]);

        $mailable = new DueTodayTransactions($transactions);

        $html = $mailable->render();

        $this->assertStringContainsString('Rent Payment', $html);
    }
}
