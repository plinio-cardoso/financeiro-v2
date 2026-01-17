<?php

namespace Tests\Feature\Commands;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Mail\OverdueTransactions;
use App\Models\NotificationSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyOverdueCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully(): void
    {
        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => false,
        ]);

        $this->artisan('notify:overdue')
            ->assertExitCode(0);
    }

    public function test_command_sends_notifications_for_overdue(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('Checking for overdue transactions...')
            ->expectsOutput('Notifications sent for 1 overdue transactions.')
            ->assertExitCode(0);

        Mail::assertQueued(OverdueTransactions::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_command_only_sends_for_pending_debits(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        // Pending debit overdue - should be included
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        // Paid debit overdue - should be excluded
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'due_date' => today()->subDays(5),
        ]);

        // Credit overdue - should be excluded
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('Notifications sent for 1 overdue transactions.')
            ->assertExitCode(0);
    }

    public function test_command_respects_notification_settings(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => false, // Disabled
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('No overdue transactions or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_returns_success_code(): void
    {
        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        $this->artisan('notify:overdue')
            ->assertExitCode(0);
    }

    public function test_command_outputs_count(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->for($user)->count(3)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('Notifications sent for 3 overdue transactions.')
            ->assertExitCode(0);
    }

    public function test_command_does_nothing_when_no_transactions(): void
    {
        Mail::fake();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('Checking for overdue transactions...')
            ->expectsOutput('No overdue transactions or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_does_nothing_when_no_emails_configured(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => [], // No emails
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('No overdue transactions or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_does_not_send_for_current_transactions(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('No overdue transactions or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_does_not_send_for_future_transactions(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->addDays(5),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('No overdue transactions or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_sends_for_multiple_overdue_transactions(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        // Overdue by 1 day
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDay(),
        ]);

        // Overdue by 10 days
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(10),
        ]);

        // Overdue by 30 days
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(30),
        ]);

        $this->artisan('notify:overdue')
            ->expectsOutput('Notifications sent for 3 overdue transactions.')
            ->assertExitCode(0);
    }
}
