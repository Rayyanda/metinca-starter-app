<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    //generate division code on creating
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($division) {
            if (empty($division->code)) {
                $division->code = 'DIV-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    // PO Internals created by this user
    public function poInternals()
    {
        return $this->hasMany(POInternal::class, 'created_by');
    }

    // Batches created by this user
    public function batches()
    {
        return $this->hasMany(Batch::class, 'created_by');
    }

    // Batch operations operated by this user
    public function batchOperations()
    {
        return $this->hasMany(BatchOperation::class, 'operator_id');
    }

    // Quality checks performed by this user
    public function qualityChecks()
    {
        return $this->hasMany(QualityCheck::class, 'checked_by');
    }

    // Approvals by this user
    public function approvals()
    {
        return $this->hasMany(BatchApproval::class, 'approver_id');
    }

    // Machine downtimes reported by this user
    public function reportedDowntimes()
    {
        return $this->hasMany(MachineDowntime::class, 'reported_by');
    }

    // Machine downtimes resolved by this user
    public function resolvedDowntimes()
    {
        return $this->hasMany(MachineDowntime::class, 'resolved_by');
    }

    

    public function scopeByRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    public function scopeByDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    // Helper methods
    


    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
