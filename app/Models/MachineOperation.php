<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineOperation extends Model
{
    //
    protected $fillable = [
        'machine_id',
        'operation_id',
        'estimated_duration_minutes',
        'hourly_rate',
        'setup_time_minutes',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    // Helper methods
    public function getTotalDuration()
    {
        return $this->setup_time_minutes + $this->estimated_duration_minutes;
    }
}
