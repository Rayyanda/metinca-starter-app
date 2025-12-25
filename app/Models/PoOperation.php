<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoOperation extends Model
{
    //
    protected $table = 'po_operations';

    protected $fillable = [
        'po_internal_id',
        'operation_id',
        'estimated_duration_minutes',
        'sequence_order',
        'notes',
    ];

    // Relationships
    public function poInternal()
    {
        return $this->belongsTo(POInternal::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }
}
