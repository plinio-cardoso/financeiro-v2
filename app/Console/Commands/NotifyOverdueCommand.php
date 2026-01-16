<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotifyOverdueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for overdue transactions';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking for overdue transactions...');

        $count = $notificationService->sendOverdueNotifications(new \DateTime);

        if ($count > 0) {
            $this->info("Notifications sent for {$count} overdue transactions.");
        } else {
            $this->info('No overdue transactions or notifications disabled.');
        }

        return self::SUCCESS;
    }
}
