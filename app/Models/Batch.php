<?php

// app/Models/Batch.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'batch_no', 'part_no', 'description', 'planned_qty',
        'completed_qty', 'current_division_id', 'status'
    ];
    
    public function currentDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'current_division_id');
    }
    
    public function operationLogs(): HasMany
    {
        return $this->hasMany(BatchOperationLog::class)->orderBy('sequence');
    }
    
    public function currentOperation()
    {
        return $this->operationLogs()
            ->whereIn('status', ['pending', 'ready', 'running'])
            ->orderBy('sequence')
            ->first();
    }
    
    // Hitung progress
    public function getProgressAttribute()
    {
        $totalOps = $this->operationLogs()->count();
        $completedOps = $this->operationLogs()->where('status', 'completed')->count();
        
        return $totalOps > 0 ? round(($completedOps / $totalOps) * 100) : 0;
    }
    
    // Estimasi waktu selesai
    public function getEstimatedCompletionAttribute()
    {
        $remainingTime = $this->operationLogs()
            ->where('status', '!=', 'completed')
            ->sum('divisionOperation.std_time');
            
        return now()->addMinutes($remainingTime * $this->planned_qty);
    }
}