<?php

namespace App\Services;

use App\Mail\DueTodayTransactions;
use App\Mail\OverdueTransactions;
use App\Models\NotificationSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notifications for transactions due today
     */
    public function sendDueTodayNotifications(\DateTime $date): int
    {
        if (! $this->shouldSendNotification('due_today')) {
            return 0;
        }

        $transactions = Transaction::pending()
            ->debits()
            ->dueToday()
            ->with(['tags', 'user'])
            ->get();

        if ($transactions->isEmpty()) {
            return 0;
        }

        $emails = $this->getNotificationEmails();

        if (empty($emails)) {
            Log::warning('No emails configured for notifications');

            return 0;
        }

        Mail::to($emails)->send(new DueTodayTransactions($transactions));

        Log::info('Due today notifications sent', [
            'count' => $transactions->count(),
            'total' => $transactions->sum('amount'),
            'emails' => count($emails),
        ]);

        return $transactions->count();
    }

    /**
     * Send notifications for overdue transactions
     */
    public function sendOverdueNotifications(\DateTime $date): int
    {
        if (! $this->shouldSendNotification('overdue')) {
            return 0;
        }

        $transactions = Transaction::overdue()
            ->debits()
            ->with(['tags', 'user'])
            ->get();

        if ($transactions->isEmpty()) {
            return 0;
        }

        $emails = $this->getNotificationEmails();

        if (empty($emails)) {
            Log::warning('No emails configured for notifications');

            return 0;
        }

        Mail::to($emails)->send(new OverdueTransactions($transactions));

        Log::info('Overdue notifications sent', [
            'count' => $transactions->count(),
            'total' => $transactions->sum('amount'),
            'emails' => count($emails),
        ]);

        return $transactions->count();
    }

    /**
     * Check if notifications should be sent for given type
     */
    private function shouldSendNotification(string $type): bool
    {
        $settings = NotificationSetting::getSettings();

        return match ($type) {
            'due_today' => $settings->notify_due_today,
            'overdue' => $settings->notify_overdue,
            default => false,
        };
    }

    /**
     * Get notification recipient emails
     */
    private function getNotificationEmails(): array
    {
        $settings = NotificationSetting::getSettings();

        return $settings->emails ?? [];
    }
}
