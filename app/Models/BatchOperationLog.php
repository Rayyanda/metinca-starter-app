<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchOperationLog extends Model
{
    protected $fillable = [
        'batch_id', 'division_operation_id', 'sequence',
        'planned_start', 'planned_end', 'actual_start', 'actual_end',
        'duration', 'processed_qty', 'good_qty', 'reject_qty',
        'operator_id', 'machine_code', 'status', 'notes'
    ];
    
    protected $casts = [
        'planned_start' => 'datetime',
        'planned_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];
    
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
    
    public function divisionOperation(): BelongsTo
    {
        return $this->belongsTo(DivisionOperation::class);
    }
    
    // Hitung durasi aktual
    public function getActualDurationAttribute()
    {
        if ($this->actual_start && $this->actual_end) {
            return $this->actual_end->diffInMinutes($this->actual_start);
        }
        return null;
    }
    
    // Cek jika bisa di-start
    public function canStart()
    {
        // Cek jika operasi sebelumnya sudah selesai
        if ($this->sequence > 1) {
            $prevOp = BatchOperationLog::where('batch_id', $this->batch_id)
                ->where('sequence', $this->sequence - 1)
                ->first();
                
            if (!$prevOp || $prevOp->status != 'completed') {
                return false;
            }
        }
        
        return $this->status == 'pending' || $this->status == 'ready';
    }
}