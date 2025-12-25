<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchApproval extends Model
{
    //
    protected $fillable = [
        'batch_id',
        'approval_type',
        'status',
        'approver_id',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'approval_level',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('approval_level', $level);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
