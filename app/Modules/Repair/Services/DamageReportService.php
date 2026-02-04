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
    /**
     * Define role-based transition rules
     * Format: [from_status => [role => [allowed_to_statuses]]]
     */
    protected const ROLE_TRANSITION_RULES = [
        DamageReport::STATUS_UPLOADED_BY_OPERATOR => [
            'repair.supervisor' => [DamageReport::STATUS_RECEIVED_BY_FOREMAN],
        ],
        DamageReport::STATUS_RECEIVED_BY_FOREMAN => [
            'repair.manager' => [DamageReport::STATUS_APPROVED_BY_MANAGER],
        ],
        DamageReport::STATUS_APPROVED_BY_MANAGER => [
            'repair.technician' => [DamageReport::STATUS_ON_FIXING_PROGRESS],
        ],
        DamageReport::STATUS_ON_FIXING_PROGRESS => [
            'repair.technician' => [DamageReport::STATUS_DONE_FIXING],
        ],
        DamageReport::STATUS_DONE_FIXING => [],
    ];

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
                'assigned_technician_id' => $data['assigned_technician_id'] ?? null,
                'department' => $data['department'],
                'location' => $data['location'] ?? ($machine->location ?? ''),
                'section' => $data['section'] ?? '',
                'damage_type' => $data['damage_type'],
                'damage_type_other' => $data['damage_type_other'] ?? null,
                'description' => $data['description'],
                'priority' => $priority,
                'status' => DamageReport::STATUS_UPLOADED_BY_OPERATOR,
                'reported_at' => $reportedAt,
                'target_completed_at' => $targetDate,
            ]);

            $this->addAttachments($report, $beforePhotos, Attachment::TYPE_BEFORE, $reporter);

            ReportHistory::create([
                'damage_report_id' => $report->id,
                'actor_id' => $reporter->id,
                'action' => 'created',
                'from_status' => null,
                'to_status' => DamageReport::STATUS_UPLOADED_BY_OPERATOR,
                'notes' => $report->assigned_technician_id
                    ? 'Report created and assigned to technician.'
                    : 'Report created. Awaiting manager approval and technician assignment.',
            ]);

            return $report;
        });
    }

    public function updateStatus(
        DamageReport $report,
        string $newStatus,
        User $actor,
        ?string $notes = null,
        array $afterPhotos = [],
        ?int $assignedTechnicianId = null
    ): DamageReport {
        // Validate transition is allowed for this user's role
        if (!$this->canUserTransitionTo($report, $newStatus, $actor)) {
            $userRoles = $actor->roles->pluck('name')->join(', ');
            throw new \InvalidArgumentException(
                "User with role(s) [{$userRoles}] cannot transition from [{$report->status}] to [{$newStatus}]."
            );
        }

        return DB::transaction(function () use ($report, $newStatus, $actor, $notes, $afterPhotos, $assignedTechnicianId) {
            $fromStatus = $report->status;
            $report->status = $newStatus;

            // Track who performed each action and when
            switch ($newStatus) {
                case DamageReport::STATUS_RECEIVED_BY_FOREMAN:
                    $report->received_by_foreman_id = $actor->id;
                    $report->received_by_foreman_at = Carbon::now();
                    break;

                case DamageReport::STATUS_APPROVED_BY_MANAGER:
                    $report->approved_by_manager_id = $actor->id;
                    $report->approved_by_manager_at = Carbon::now();
                    // Assign technician if provided
                    if ($assignedTechnicianId) {
                        $report->assigned_technician_id = $assignedTechnicianId;
                    }
                    break;

                case DamageReport::STATUS_ON_FIXING_PROGRESS:
                    $report->started_fixing_at = Carbon::now();
                    break;

                case DamageReport::STATUS_DONE_FIXING:
                case DamageReport::STATUS_DONE: // Backward compatibility
                    $report->actual_completed_at = Carbon::now();
                    $this->addAttachments($report, $afterPhotos, Attachment::TYPE_AFTER, $actor);
                    break;
            }

            $report->save();

            // Create detailed history record
            ReportHistory::create([
                'damage_report_id' => $report->id,
                'actor_id' => $actor->id,
                'action' => 'status_change',
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'notes' => $notes ?? $this->getDefaultTransitionNote($fromStatus, $newStatus, $actor),
            ]);

            return $report;
        });
    }

    /**
     * Generate descriptive note for status transitions
     */
    protected function getDefaultTransitionNote(string $fromStatus, string $toStatus, User $actor): string
    {
        return match ($toStatus) {
            DamageReport::STATUS_RECEIVED_BY_FOREMAN => "Foreman {$actor->name} acknowledged receipt",
            DamageReport::STATUS_APPROVED_BY_MANAGER => "Manager {$actor->name} approved for repair",
            DamageReport::STATUS_ON_FIXING_PROGRESS => "Technician {$actor->name} started repair work",
            DamageReport::STATUS_DONE_FIXING => "Technician {$actor->name} completed repair",
            default => "Status changed by {$actor->name}",
        };
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

    /**
     * Check if transition is valid (status flow only, not role-aware)
     * @deprecated Use canUserTransitionTo() instead
     */
    public function canTransitionTo(DamageReport $report, string $newStatus): bool
    {
        $allowedTransitions = [
            DamageReport::STATUS_UPLOADED_BY_OPERATOR => [
                DamageReport::STATUS_RECEIVED_BY_FOREMAN,
            ],
            DamageReport::STATUS_RECEIVED_BY_FOREMAN => [
                DamageReport::STATUS_APPROVED_BY_MANAGER,
            ],
            DamageReport::STATUS_APPROVED_BY_MANAGER => [
                DamageReport::STATUS_ON_FIXING_PROGRESS,
            ],
            DamageReport::STATUS_ON_FIXING_PROGRESS => [
                DamageReport::STATUS_DONE_FIXING,
            ],
            DamageReport::STATUS_DONE_FIXING => [],

            // Backward compatibility
            DamageReport::STATUS_WAITING => [
                DamageReport::STATUS_IN_PROGRESS,
                DamageReport::STATUS_DONE,
            ],
            DamageReport::STATUS_IN_PROGRESS => [
                DamageReport::STATUS_DONE,
            ],
            DamageReport::STATUS_DONE => [],
        ];

        return in_array($newStatus, $allowedTransitions[$report->status] ?? [], true);
    }

    /**
     * Check if user's role allows transition to new status
     */
    public function canUserTransitionTo(DamageReport $report, string $newStatus, User $user): bool
    {
        // Super admin can do anything
        if ($user->hasRole('super_admin')) {
            return $this->canTransitionTo($report, $newStatus);
        }

        // First check if transition is valid in workflow
        if (!$this->canTransitionTo($report, $newStatus)) {
            return false;
        }

        // Get allowed transitions for this status
        $statusRules = self::ROLE_TRANSITION_RULES[$report->status] ?? [];

        // Check each role the user has
        foreach ($user->roles as $role) {
            $allowedStatuses = $statusRules[$role->name] ?? [];
            if (in_array($newStatus, $allowedStatuses, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get allowed transitions for a specific user
     */
    public function getAllowedTransitionsForUser(DamageReport $report, User $user): array
    {
        // Super admin sees all possible transitions
        if ($user->hasRole('super_admin')) {
            return $report->allowedNextStatuses();
        }

        $allowed = [];
        $statusRules = self::ROLE_TRANSITION_RULES[$report->status] ?? [];

        foreach ($user->roles as $role) {
            $roleTransitions = $statusRules[$role->name] ?? [];
            $allowed = array_merge($allowed, $roleTransitions);
        }

        return array_unique($allowed);
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
