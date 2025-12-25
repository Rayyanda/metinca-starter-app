<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcRejection extends Model
{
    //
    protected $fillable = [
        'quality_check_id',
        'batch_operation_id',
        'rejected_quantity',
        'total_quantity',
        'reject_reason',
        'action_taken',
        'rework_assigned_to',
        'rework_deadline',
        'rework_status',
        'rework_completed_at',
        'resolution_notes',
    ];

    protected $casts = [
        'rework_deadline' => 'datetime',
        'rework_completed_at' => 'datetime',
    ];

    // Relationships
    public function qualityCheck()
    {
        return $this->belongsTo(QualityCheck::class);
    }

    public function batchOperation()
    {
        return $this->belongsTo(BatchOperation::class);
    }

    public function reworkAssignee()
    {
        return $this->belongsTo(User::class, 'rework_assigned_to');
    }

    // Scopes
    public function scopePendingRework($query)
    {
        return $query->where('rework_status', 'pending');
    }

    public function scopeInProgressRework($query)
    {
        return $query->where('rework_status', 'in_progress');
    }

    public function scopeCompletedRework($query)
    {
        return $query->where('rework_status', 'completed');
    }

    // Helper methods
    public function getRejectRate()
    {
        if ($this->total_quantity == 0) {
            return 0;
        }
        return round(($this->rejected_quantity / $this->total_quantity) * 100, 2);
    }

    public function isOverdue()
    {
        return $this->rework_deadline && 
               now()->isAfter($this->rework_deadline) && 
               $this->rework_status !== 'completed';
    }
}
