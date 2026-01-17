<?php

namespace Tests\Feature\Services;

use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use App\Mail\DueTodayTransactions;
use App\Mail\OverdueTransactions;
use App\Models\NotificationSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = app(NotificationService::class);
    }

    public function test_send_due_today_notifications_sends_emails(): void
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

        $count = $this->notificationService->sendDueTodayNotifications(new \DateTime);

        $this->assertEquals(1, $count);

        Mail::assertQueued(DueTodayTransactions::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_send_due_today_notifications_only_sends_for_debits(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        // Debit due today
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        // Credit due today (should be ignored)
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Credit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        $count = $this->notificationService->sendDueTodayNotifications(new \DateTime);

        $this->assertEquals(1, $count);
    }

    public function test_send_due_today_notifications_only_sends_for_pending(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => true,
            'notify_overdue' => false,
        ]);

        // Pending debit
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today(),
        ]);

        // Paid debit (should be ignored)
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'due_date' => today(),
        ]);

        $count = $this->notificationService->sendDueTodayNotifications(new \DateTime);

        $this->assertEquals(1, $count);
    }

    public function test_send_due_today_notifications_respects_settings(): void
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

        $count = $this->notificationService->sendDueTodayNotifications(new \DateTime);

        $this->assertEquals(0, $count);

        Mail::assertNothingSent();
    }

    public function test_send_due_today_notifications_returns_count(): void
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

        $count = $this->notificationService->sendDueTodayNotifications(new \DateTime);

        $this->assertEquals(3, $count);
    }

    public function test_send_due_today_notifications_returns_zero_when_no_emails_configured(): void
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

        $count = $this->notificationService->sendDueTodayNotifications(new \DateTime);

        $this->assertEquals(0, $count);

        Mail::assertNothingSent();
    }

    public function test_send_overdue_notifications_sends_emails(): void
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

        $count = $this->notificationService->sendOverdueNotifications(new \DateTime);

        $this->assertEquals(1, $count);

        Mail::assertQueued(OverdueTransactions::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_send_overdue_notifications_only_sends_for_pending(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        // Pending overdue
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        // Paid overdue (should be ignored)
        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Paid,
            'due_date' => today()->subDays(5),
        ]);

        $count = $this->notificationService->sendOverdueNotifications(new \DateTime);

        $this->assertEquals(1, $count);
    }

    public function test_send_overdue_notifications_respects_settings(): void
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

        $count = $this->notificationService->sendOverdueNotifications(new \DateTime);

        $this->assertEquals(0, $count);

        Mail::assertNothingSent();
    }

    public function test_send_overdue_notifications_sends_to_multiple_emails(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationSetting::create([
            'emails' => ['test1@example.com', 'test2@example.com'],
            'notify_due_today' => false,
            'notify_overdue' => true,
        ]);

        Transaction::factory()->for($user)->create([
            'type' => TransactionTypeEnum::Debit,
            'status' => TransactionStatusEnum::Pending,
            'due_date' => today()->subDays(5),
        ]);

        $count = $this->notificationService->sendOverdueNotifications(new \DateTime);

        $this->assertEquals(1, $count);

        Mail::assertQueued(OverdueTransactions::class, function ($mail) {
            return $mail->hasTo('test1@example.com') && $mail->hasTo('test2@example.com');
        });
    }
}
