<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    //
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    
    public function parameters()
    {
        return $this->hasMany(OperationParameter::class);
    }
    
    public function timelogs()
    {
        return $this->hasMany(OperationTimelog::class)->orderBy('log_time');
    }
    
    // Hitung durasi setup
    public function getSetupDurationAttribute()
    {
        $start = $this->timelogs->where('log_type', 'setup_start')->first();
        $end = $this->timelogs->where('log_type', 'setup_end')->first();
        
        if ($start && $end) {
            return $end->log_time->diffInMinutes($start->log_time);
        }
        return $this->setup_time_act;
    }
    
    // Hitung durasi produksi
    public function getProductionDurationAttribute()
    {
        $start = $this->timelogs->where('log_type', 'production_start')->first();
        $end = $this->timelogs->where('log_type', 'production_end')->first();
        
        if ($start && $end) {
            return $end->log_time->diffInMinutes($start->log_time);
        }
        return $this->process_time_act;
    }
}
