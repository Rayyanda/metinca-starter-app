<?php

namespace App\Modules\Repair\Console\Commands;

use App\Modules\Repair\Services\Contracts\DamageReportServiceInterface;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'repair:deadline-reminders';

    protected $description = 'Send A-2, A-1, and overdue reminders for damage reports';

    public function handle(DamageReportServiceInterface $service): int
    {
        $count = $service->sendDeadlineReminders();

        $this->info("Notifications dispatched: {$count}");

        return self::SUCCESS;
    }
}
