<?php

namespace App\Console\Commands;

use App\Services\Fcm\FcmService;
use Illuminate\Console\Command;

class SendMoodReminder extends Command
{
    protected $signature   = 'mindease:send-mood-reminder';
    protected $description = 'Send daily mood reminder push notifications to all users';

    public function handle(FcmService $fcmService): int
    {
        $this->info('Sending daily mood reminders...');

        $fcmService->sendDailyMoodReminders();

        $this->info('Daily mood reminders dispatched successfully.');

        return self::SUCCESS;
    }
}
