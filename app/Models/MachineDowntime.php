<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineDowntime extends Model
{
    //
    protected $fillable = [
        'machine_id',
        'downtime_type',
        'started_at',
        'ended_at',
        'reason',
        'resolution_notes',
        'reported_by',
        'resolved_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Relationships
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeOngoing($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('ended_at');
    }

    // Helper methods
    public function isOngoing()
    {
        return is_null($this->ended_at);
    }

    public function getDurationMinutes()
    {
        $end = $this->ended_at ?? now();
        return $this->started_at->diffInMinutes($end);
    }

    public function getDurationHours()
    {
        return round($this->getDurationMinutes() / 60, 2);
    }
}
