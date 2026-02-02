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
        'department',
        'location',
        'section',
        'damage_type',
        'damage_type_other',
        'description',
        'priority',
        'status',
        'reported_at',
        'target_completed_at',
        'actual_completed_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
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

    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_WAITING => [self::STATUS_IN_PROGRESS, self::STATUS_DONE],
            self::STATUS_IN_PROGRESS => [self::STATUS_DONE],
            default => [],
        };
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
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
            self::STATUS_WAITING => 'bg-secondary',
            self::STATUS_IN_PROGRESS => 'bg-primary',
            self::STATUS_DONE => 'bg-success',
            default => 'bg-secondary',
        };
    }
}
