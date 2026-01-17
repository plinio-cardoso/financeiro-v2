<?php

namespace Tests\Feature\Commands;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Mail\DueTodayTransactions;
use App\Models\NotificationSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyDueTodayCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully(): void
    {
        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => false,
        ]);

        $this->artisan('notify:due-today')
            ->assertExitCode(0);
    }

    public function test_command_sends_notifications_for_due_today(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('Checking for transactions due today...')
            ->expectsOutput('Notifications sent for 1 transactions.')
            ->assertExitCode(0);

        Mail::assertQueued(DueTodayTransactions::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_command_only_sends_for_pending_debits(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        // Pending debit - should be included
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        // Paid debit - should be excluded
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'due_date' => today(),
        ]);

        // Credit - should be excluded
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('Notifications sent for 1 transactions.')
            ->assertExitCode(0);
    }

    public function test_command_respects_notification_settings(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false, // Disabled
            'notify_overdue' => false,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('No transactions due today or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_returns_success_code(): void
    {
        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $this->artisan('notify:due-today')
            ->assertExitCode(0);
    }

    public function test_command_outputs_count(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        Transaction::factory()->for($user)->count(3)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('Notifications sent for 3 transactions.')
            ->assertExitCode(0);
    }

    public function test_command_does_nothing_when_no_transactions(): void
    {
        Mail::fake();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('Checking for transactions due today...')
            ->expectsOutput('No transactions due today or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_does_nothing_when_no_emails_configured(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => [], // No emails
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('No transactions due today or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_does_not_send_for_yesterday(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDay(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('No transactions due today or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }

    public function test_command_does_not_send_for_tomorrow(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->addDay(),
        ]);

        $this->artisan('notify:due-today')
            ->expectsOutput('No transactions due today or notifications disabled.')
            ->assertExitCode(0);

        Mail::assertNothingSent();
    }
}
