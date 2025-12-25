<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchOperation extends Model
{
    //
    protected $fillable = [
        'batch_id',
        'operation_id',
        'machine_id',
        'sequence_order',
        'estimated_duration_minutes',
        'status',
        'estimated_start_at',
        'estimated_completion_at',
        'actual_start_at',
        'actual_completion_at',
        'paused_at',
        'paused_reason',
        'resumed_at',
        'operator_id',
        'actual_good_quantity',
        'actual_reject_quantity',
        'operator_notes',
    ];

    protected $casts = [
        'estimated_start_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'actual_completion_at' => 'datetime',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
    ];

    // Relationships
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function qualityChecks()
    {
        return $this->hasMany(QualityCheck::class);
    }

    public function histories()
    {
        return $this->hasMany(OperationHistory::class)->orderBy('action_at');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeQCPending($query)
    {
        return $query->where('status', 'qc_pending');
    }

    public function scopeByDivision($query, $divisionId)
    {
        return $query->whereHas('operation', function($q) use ($divisionId) {
            $q->where('division_id', $divisionId);
        });
    }

    // Helper methods
    public function canStart()
    {
        return in_array($this->status, ['ready', 'pending']);
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isPaused()
    {
        return !is_null($this->paused_at) && is_null($this->resumed_at);
    }

    public function getActualDurationMinutes()
    {
        if (!$this->actual_start_at || !$this->actual_completion_at) {
            return null;
        }
        return $this->actual_start_at->diffInMinutes($this->actual_completion_at);
    }

    public function getVarianceMinutes()
    {
        $actual = $this->getActualDurationMinutes();
        if (is_null($actual)) {
            return null;
        }
        return $actual - $this->estimated_duration_minutes;
    }

    public function getEfficiencyPercentage()
    {
        $variance = $this->getVarianceMinutes();
        if (is_null($variance) || $this->estimated_duration_minutes == 0) {
            return null;
        }
        return round((1 - ($variance / $this->estimated_duration_minutes)) * 100, 2);
    }

    public function requiresQCBefore()
    {
        return $this->operation->requires_qc_before;
    }

    public function requiresQCAfter()
    {
        return $this->operation->requires_qc_after;
    }

    public function getLastQC()
    {
        return $this->qualityChecks()->latest('checked_at')->first();
    }
}
