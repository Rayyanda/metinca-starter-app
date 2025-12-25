<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    //
    protected $fillable = [
        'division_id',
        'name',
        'code',
        'machine_type',
        'status',
        'max_concurrent_operations',
        'current_operations',
        'specifications',
        'is_active',
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function operations()
    {
        return $this->belongsToMany(Operation::class, 'machine_operations')
            ->withPivot([
                'estimated_duration_minutes',
                'hourly_rate',
                'setup_time_minutes',
                'is_default',
                'is_active'
            ])
            ->withTimestamps();
    }

    public function batchOperations()
    {
        return $this->hasMany(BatchOperation::class);
    }

    public function downtimes()
    {
        return $this->hasMany(MachineDowntime::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                     ->whereColumn('current_operations', '<', 'max_concurrent_operations');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeBrokenDown($query)
    {
        return $query->where('status', 'breakdown');
    }

    // Helper methods
    public function isAvailable()
    {
        return $this->status === 'available' && 
               $this->current_operations < $this->max_concurrent_operations;
    }

    public function canAcceptOperation()
    {
        return $this->isAvailable() && $this->is_active;
    }

    public function incrementOperations()
    {
        $this->increment('current_operations');
        if ($this->current_operations >= $this->max_concurrent_operations) {
            $this->update(['status' => 'in_use']);
        }
    }

    public function decrementOperations()
    {
        $this->decrement('current_operations');
        if ($this->current_operations < $this->max_concurrent_operations) {
            $this->update(['status' => 'available']);
        }
    }
}
