<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoInternal extends Model
{
    //
    protected $table = 'po_internals';

    protected $fillable = [
        'po_number',
        'customer_name',
        'product_description',
        'quantity',
        'due_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function operations()
    {
        return $this->hasMany(POOperation::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeInProduction($query)
    {
        return $query->where('status', 'in_production');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function getRemainingQuantity()
    {
        $batchedQuantity = $this->batches()->sum('quantity');
        return $this->quantity - $batchedQuantity;
    }

    public function isFullyBatched()
    {
        return $this->getRemainingQuantity() <= 0;
    }

    public function getTotalEstimatedDuration()
    {
        return $this->operations()->sum('estimated_duration_minutes');
    }
}
