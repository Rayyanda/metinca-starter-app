<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    //
    protected $fillable = [
        'batch_number',
        'po_internal_id',
        'quantity',
        'priority',
        'is_rush_order',
        'status',
        'current_operation_id',
        'estimated_completion_at',
        'actual_completion_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'is_rush_order' => 'boolean',
        'estimated_completion_at' => 'datetime',
        'actual_completion_at' => 'datetime',
    ];

    // Relationships
    public function poInternal()
    {
        return $this->belongsTo(POInternal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentOperation()
    {
        return $this->belongsTo(Operation::class, 'current_operation_id');
    }

    public function operations()
    {
        return $this->hasMany(BatchOperation::class)->orderBy('sequence_order');
    }

    public function approvals()
    {
        return $this->hasMany(BatchApproval::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRushOrder($query)
    {
        return $query->where('is_rush_order', true);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderByDesc('priority')
                     ->orderBy('created_at');
    }

    // Helper methods
    public function getProgressPercentage()
    {
        $total = $this->operations()->count();
        $completed = $this->operations()->where('status', 'completed')->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    public function getCurrentOperationModel()
    {
        return $this->operations()
            ->whereIn('status', ['in_progress', 'qc_pending'])
            ->first();
    }

    public function getNextOperation()
    {
        $current = $this->getCurrentOperationModel();
        if (!$current) {
            return $this->operations()->where('status', 'pending')->orderBy('sequence_order')->first();
        }

        return $this->operations()
            ->where('sequence_order', '>', $current->sequence_order)
            ->orderBy('sequence_order')
            ->first();
    }

    public function needsApproval()
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved()
    {
        return in_array($this->status, ['approved', 'released', 'in_progress', 'completed']);
    }
}
