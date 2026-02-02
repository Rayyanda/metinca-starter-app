<?php

namespace App\Modules\Repair\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'department',
        'location',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(DamageReport::class);
    }
}
