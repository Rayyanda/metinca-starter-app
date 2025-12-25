<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationHistory extends Model
{
    //
    protected $fillable = [
        'batch_operation_id',
        'action',
        'previous_status',
        'new_status',
        'action_by',
        'action_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function batchOperation()
    {
        return $this->belongsTo(BatchOperation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('action_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public static function log($batchOperationId, $action, $previousStatus, $newStatus, $userId, $notes = null, $metadata = [])
    {
        return self::create([
            'batch_operation_id' => $batchOperationId,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'action_by' => $userId,
            'action_at' => now(),
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }
}
