<?php

namespace App\Modules\Repair\Services;

use App\Models\User;
use App\Modules\Repair\Models\Attachment;
use App\Modules\Repair\Models\DamageReport;
use App\Modules\Repair\Models\Machine;
use App\Modules\Repair\Models\ReportHistory;
use App\Modules\Repair\Notifications\DeadlineReminderNotification;
use App\Modules\Repair\Repositories\Contracts\DamageReportRepositoryInterface;
use App\Modules\Repair\Services\Contracts\DamageReportServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DamageReportService implements DamageReportServiceInterface
{
    public function __construct(
        protected DamageReportRepositoryInterface $repository
    ) {}

    public function getFilteredReports(array $filters, User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters, $user, $perPage);
    }

    public function createReport(array $data, User $reporter, array $beforePhotos = []): DamageReport
    {
        return DB::transaction(function () use ($data, $reporter, $beforePhotos) {
            $reportedAt = Carbon::now();
            $priority = $data['priority'];
            $targetDate = isset($data['target_completed_at']) && $data['target_completed_at']
                ? Carbon::parse($data['target_completed_at'])
                : $reportedAt->copy()->addDays(DamageReport::defaultTargetDays($priority));

            $machine = Machine::findOrFail($data['machine_id']);

            $report = $this->repository->create([
                'report_code' => DamageReport::generateCode($reportedAt),
                'machine_id' => $data['machine_id'],
                'reported_by' => $reporter->id,
                'assigned_technician_id' => $data['assigned_technician_id'],
                'department' => $data['department'],
                'location' => $data['location'] ?? ($machine->location ?? ''),
                'section' => $data['section'] ?? '',
                'damage_type' => $data['damage_type'],
                'damage_type_other' => $data['damage_type_other'] ?? null,
                'description' => $data['description'],
                'priority' => $priority,
                'status' => DamageReport::STATUS_WAITING,
                'reported_at' => $reportedAt,
                'target_completed_at' => $targetDate,
            ]);

            $this->addAttachments($report, $beforePhotos, Attachment::TYPE_BEFORE, $reporter);

            ReportHistory::create([
                'damage_report_id' => $report->id,
                'actor_id' => $reporter->id,
                'action' => 'created',
                'from_status' => null,
                'to_status' => DamageReport::STATUS_WAITING,
                'notes' => 'Report created and assigned to technician.',
            ]);

            return $report;
        });
    }

    public function updateStatus(
        DamageReport $report,
        string $newStatus,
        User $actor,
        ?string $notes = null,
        array $afterPhotos = []
    ): DamageReport {
        if (!$this->canTransitionTo($report, $newStatus)) {
            throw new \InvalidArgumentException('Invalid status transition.');
        }

        return DB::transaction(function () use ($report, $newStatus, $actor, $notes, $afterPhotos) {
            $fromStatus = $report->status;
            $report->status = $newStatus;

            if ($newStatus === DamageReport::STATUS_DONE) {
                $report->actual_completed_at = Carbon::now();
                $this->addAttachments($report, $afterPhotos, Attachment::TYPE_AFTER, $actor);
            }

            $report->save();

            ReportHistory::create([
                'damage_report_id' => $report->id,
                'actor_id' => $actor->id,
                'action' => 'status_change',
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'notes' => $notes,
            ]);

            return $report;
        });
    }

    public function addAttachments(DamageReport $report, array $files, string $type, User $uploader): void
    {
        foreach ($files as $file) {
            $path = $file->store("damage_reports/{$report->report_code}/{$type}", 'public');

            Attachment::create([
                'damage_report_id' => $report->id,
                'type' => $type,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            ReportHistory::create([
                'damage_report_id' => $report->id,
                'actor_id' => $uploader->id,
                'action' => $type === Attachment::TYPE_BEFORE ? 'upload_before' : 'upload_after',
                'notes' => $file->getClientOriginalName(),
            ]);
        }
    }

    public function canTransitionTo(DamageReport $report, string $newStatus): bool
    {
        $allowedTransitions = [
            DamageReport::STATUS_WAITING => [DamageReport::STATUS_IN_PROGRESS, DamageReport::STATUS_DONE],
            DamageReport::STATUS_IN_PROGRESS => [DamageReport::STATUS_DONE],
            DamageReport::STATUS_DONE => [],
        ];

        return in_array($newStatus, $allowedTransitions[$report->status] ?? [], true);
    }

    public function sendDeadlineReminders(): int
    {
        $pendingReports = $this->repository->getPendingDeadlineReports();
        $overdueReports = $this->repository->getOverdueReports();

        $supervisorsAndManagers = User::role(['repair.supervisor', 'repair.manager', 'super_admin'])->get();

        $count = 0;
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();
        $twoDays = $today->copy()->addDays(2);

        foreach ($pendingReports as $report) {
            $target = $report->target_completed_at;
            $type = $target->isSameDay($twoDays) ? 'A-2' : ($target->isSameDay($tomorrow) ? 'A-1' : null);

            if ($type) {
                $recipients = collect([$report->assignedTechnician])->filter()->merge($supervisorsAndManagers);
                Notification::send($recipients, new DeadlineReminderNotification($report, $type));
                $count += $recipients->count();
            }
        }

        foreach ($overdueReports as $report) {
            $recipients = collect([$report->assignedTechnician])->filter()->merge($supervisorsAndManagers);
            Notification::send($recipients, new DeadlineReminderNotification($report, 'OVERDUE'));
            $count += $recipients->count();
        }

        return $count;
    }
}
