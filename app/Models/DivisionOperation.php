<?php

// app/Models/DivisionOperation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DivisionOperation extends Model
{
    protected $fillable = [
        'division_id', 'code', 'name', 'sequence', 
        'std_time', 'machine_type', 'parameters'
    ];
    
    protected $casts = [
        'parameters' => 'array'
    ];
    
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
    
    public function operationLogs(): HasMany
    {
        return $this->hasMany(BatchOperationLog::class);
    }
}
