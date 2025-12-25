<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Operation extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'division_id',
        'name',
        'code',
        'estimated_duration_minutes',
        'requires_qc_before',
        'requires_qc_after',
        'sequence_order',
        'is_active',
    ];

    protected $casts = [
        'requires_qc_before' => 'boolean',
        'requires_qc_after' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_operations')
            ->withPivot([
                'estimated_duration_minutes',
                'hourly_rate',
                'setup_time_minutes',
                'is_default',
                'is_active'
            ])
            ->withTimestamps();
    }

    // Flow relationships
    public function nextOperations()
    {
        return $this->belongsToMany(
            Operation::class, 
            'operation_flows', 
            'from_operation_id', 
            'to_operation_id'
        )->withPivot('sequence_order', 'is_active')
         ->withTimestamps();
    }

    public function previousOperations()
    {
        return $this->belongsToMany(
            Operation::class, 
            'operation_flows', 
            'to_operation_id', 
            'from_operation_id'
        )->withPivot('sequence_order', 'is_active')
         ->withTimestamps();
    }

    public function poOperations()
    {
        return $this->hasMany(POOperation::class);
    }

    public function batchOperations()
    {
        return $this->hasMany(BatchOperation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderBySequence($query)
    {
        return $query->orderBy('sequence_order');
    }

    // Helper methods
    public function getDefaultMachine()
    {
        return $this->machines()
            ->wherePivot('is_default', true)
            ->wherePivot('is_active', true)
            ->first();
    }

    public function getAvailableMachines()
    {
        return $this->machines()
            ->wherePivot('is_active', true)
            ->where('machines.is_active', true)
            ->where('machines.status', 'available')
            ->get();
    }
}
