<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationFlow extends Model
{
    //
    protected $fillable = [
        'from_operation_id',
        'to_operation_id',
        'sequence_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function fromOperation()
    {
        return $this->belongsTo(Operation::class, 'from_operation_id');
    }

    public function toOperation()
    {
        return $this->belongsTo(Operation::class, 'to_operation_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
