<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyDueTodayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:due-today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for transactions due today';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking for transactions due today...');

        $count = $notificationService->sendDueTodayNotifications(new \DateTime);

        if ($count > 0) {
            $this->info("Notifications sent for {$count} transactions.");
        } else {
            $this->info('No transactions due today or notifications disabled.');
        }

        return self::SUCCESS;
    }
}
