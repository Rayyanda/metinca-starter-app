<?php

namespace App\Modules\Repair\Notifications;

use App\Modules\Repair\Models\DamageReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly DamageReport $report,
        private readonly string $type
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'report_id' => $this->report->id,
            'report_code' => $this->report->report_code,
            'priority' => $this->report->priority,
            'status' => $this->report->status,
            'target_completed_at' => optional($this->report->target_completed_at)->toDateString(),
        ];
    }
}
