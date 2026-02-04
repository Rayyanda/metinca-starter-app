<?php

namespace App\Modules\Repair\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DamageReport extends Model
{
    use HasFactory;

    // New workflow statuses
    public const STATUS_UPLOADED_BY_OPERATOR = 'uploaded_by_operator';
    public const STATUS_RECEIVED_BY_FOREMAN = 'received_by_foreman_waiting_manager';
    public const STATUS_APPROVED_BY_MANAGER = 'approved_by_manager_waiting_technician';
    public const STATUS_ON_FIXING_PROGRESS = 'on_fixing_progress';
    public const STATUS_DONE_FIXING = 'done_fixing';

    // Backward compatibility - old statuses
    public const STATUS_WAITING = 'waiting';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'report_code',
        'machine_id',
        'reported_by',
        'assigned_technician_id',
        'received_by_foreman_id',
        'approved_by_manager_id',
        'department',
        'location',
        'section',
        'damage_type',
        'damage_type_other',
        'description',
        'priority',
        'status',
        'reported_at',
        'received_by_foreman_at',
        'approved_by_manager_at',
        'started_fixing_at',
        'target_completed_at',
        'actual_completed_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'received_by_foreman_at' => 'datetime',
        'approved_by_manager_at' => 'datetime',
        'started_fixing_at' => 'datetime',
        'target_completed_at' => 'date',
        'actual_completed_at' => 'datetime',
    ];

    public static function generateCode(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $prefix = 'LRK-' . $date->format('Ymd') . '-';
        $counter = (int) static::whereDate('reported_at', $date->toDateString())->count() + 1;

        do {
            $code = $prefix . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $counter++;
        } while (static::where('report_code', $code)->exists());

        return $code;
    }

    public static function defaultTargetDays(string $priority): int
    {
        return match ($priority) {
            self::PRIORITY_CRITICAL => 3,
            self::PRIORITY_HIGH => 10,
            self::PRIORITY_MEDIUM => 21,
            default => 30,
        };
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Get allowed next statuses (workflow-based only, not role-aware)
     * @deprecated Use DamageReportService::getAllowedTransitionsForUser() instead
     */
    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_UPLOADED_BY_OPERATOR => [self::STATUS_RECEIVED_BY_FOREMAN],
            self::STATUS_RECEIVED_BY_FOREMAN => [self::STATUS_APPROVED_BY_MANAGER],
            self::STATUS_APPROVED_BY_MANAGER => [self::STATUS_ON_FIXING_PROGRESS],
            self::STATUS_ON_FIXING_PROGRESS => [self::STATUS_DONE_FIXING],
            // Backward compatibility
            self::STATUS_WAITING => [self::STATUS_IN_PROGRESS, self::STATUS_DONE],
            self::STATUS_IN_PROGRESS => [self::STATUS_DONE],
            default => [],
        };
    }

    /**
     * Get allowed next statuses for a specific user role
     */
    public function allowedNextStatusesForRole(User $user): array
    {
        // Delegate to service layer for role-based logic
        return app(\App\Modules\Repair\Services\Contracts\DamageReportServiceInterface::class)
            ->getAllowedTransitionsForUser($this, $user);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function receivedByForeman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_foreman_id');
    }

    public function approvedByManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_manager_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function beforeAttachments(): HasMany
    {
        return $this->attachments()->where('type', Attachment::TYPE_BEFORE);
    }

    public function afterAttachments(): HasMany
    {
        return $this->attachments()->where('type', Attachment::TYPE_AFTER);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ReportHistory::class);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority', $priority))
            ->when($filters['department'] ?? null, fn($q, $department) => $q->where('department', $department))
            ->when($filters['location'] ?? null, fn($q, $location) => $q->where('location', 'like', "%{$location}%"))
            ->when($filters['machine'] ?? null, fn($q, $machine) => $q->whereHas('machine', fn($mq) => $mq->where('code', 'like', "%{$machine}%")))
            ->when($filters['from'] ?? null, fn($q, $from) => $q->whereDate('reported_at', '>=', $from))
            ->when($filters['to'] ?? null, fn($q, $to) => $q->whereDate('reported_at', '<=', $to));
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            self::PRIORITY_CRITICAL => 'bg-danger',
            self::PRIORITY_HIGH => 'bg-warning text-dark',
            self::PRIORITY_MEDIUM => 'bg-info text-dark',
            default => 'bg-primary',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_UPLOADED_BY_OPERATOR => 'bg-secondary',
            self::STATUS_RECEIVED_BY_FOREMAN => 'bg-info',
            self::STATUS_APPROVED_BY_MANAGER => 'bg-warning',
            self::STATUS_ON_FIXING_PROGRESS => 'bg-primary',
            self::STATUS_DONE_FIXING => 'bg-success',
            // Backward compatibility
            self::STATUS_WAITING => 'bg-secondary',
            self::STATUS_IN_PROGRESS => 'bg-primary',
            self::STATUS_DONE => 'bg-success',
            default => 'bg-secondary',
        };
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UPLOADED_BY_OPERATOR => 'Diupload oleh Operator',
            self::STATUS_RECEIVED_BY_FOREMAN => 'Diterima oleh Foreman',
            self::STATUS_APPROVED_BY_MANAGER => 'Disetujui oleh Manager',
            self::STATUS_ON_FIXING_PROGRESS => 'Sedang Diperbaiki',
            self::STATUS_DONE_FIXING => 'Selesai Diperbaiki',
            // Backward compatibility
            self::STATUS_WAITING => 'Menunggu',
            self::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            self::STATUS_DONE => 'Selesai',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Check if report is in a final state
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_DONE_FIXING,
            self::STATUS_DONE, // Backward compatibility
        ]);
    }
}
