<?php

namespace App\Modules\Repair\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    public const TYPE_BEFORE = 'before';
    public const TYPE_AFTER = 'after';

    protected $fillable = [
        'damage_report_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function damageReport(): BelongsTo
    {
        return $this->belongsTo(DamageReport::class);
    }
}
