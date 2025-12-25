<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityCheck extends Model
{
    //
    protected $fillable = [
        'batch_operation_id',
        'check_type',
        'result',
        'checked_quantity',
        'passed_quantity',
        'failed_quantity',
        'defect_description',
        'corrective_action',
        'checked_by',
        'checked_at',
        'notes',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    // Relationships
    public function batchOperation()
    {
        return $this->belongsTo(BatchOperation::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function rejection()
    {
        return $this->hasOne(QCRejection::class);
    }

    // Scopes
    public function scopePass($query)
    {
        return $query->where('result', 'pass');
    }

    public function scopeFail($query)
    {
        return $query->where('result', 'fail');
    }

    public function scopeBeforeStart($query)
    {
        return $query->where('check_type', 'before_start');
    }

    public function scopeAfterComplete($query)
    {
        return $query->where('check_type', 'after_complete');
    }

    // Helper methods
    public function isPassed()
    {
        return $this->result === 'pass';
    }

    public function isFailed()
    {
        return $this->result === 'fail';
    }

    public function getPassRate()
    {
        if ($this->checked_quantity == 0) {
            return 0;
        }
        return round(($this->passed_quantity / $this->checked_quantity) * 100, 2);
    }

    public function getRejectRate()
    {
        if ($this->checked_quantity == 0) {
            return 0;
        }
        return round(($this->failed_quantity / $this->checked_quantity) * 100, 2);
    }
}
