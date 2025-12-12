<?php

namespace App\Services;
use App\Models\Batch;
use App\Models\BatchFlow;
use App\Models\BatchOperationLog;
use App\Models\Division;
use App\Models\DivisionOperation;
use Illuminate\Support\Facades\Auth;


class ProductionFlowService
{
    public function startBatchInDivision(Batch $batch, Division $division)
    {
        // Cek jika batch sudah di divisi ini
        $existingFlow = BatchFlow::where('batch_id', $batch->id)
            ->where('to_division_id', $division->id)
            ->first();
            
        if ($existingFlow) {
            throw new \Exception('Batch already in this division');
        }
        
        // Cek jika ini divisi pertama atau ada flow sebelumnya
        if (!$division->is_entry_point) {
            $previousDivision = Division::where('sequence', $division->sequence - 1)->first();
            $previousFlow = BatchFlow::where('batch_id', $batch->id)
                ->where('to_division_id', $previousDivision->id)
                ->where('status', 'completed')
                ->first();
                
            if (!$previousFlow) {
                throw new \Exception('Previous division not completed yet');
            }
            
            $inputQty = $previousFlow->output_qty;
        } else {
            $inputQty = $batch->quantity;
        }
        
        // Create new flow
        $flow = BatchFlow::create([
            'batch_id' => $batch->id,
            'from_division_id' => $division->is_entry_point ? null : $previousDivision->id,
            'to_division_id' => $division->id,
            'status' => 'pending',
            'input_qty' => $inputQty,
            'received_date' => now()
        ]);
        
        // Create operation logs berdasarkan division operations
        $divisionOperations = DivisionOperation::where('division_id', $division->id)
            ->orderBy('sequence')
            ->get();
            
        foreach ($divisionOperations as $divOp) {
            BatchOperationLog::create([
                'batch_flow_id' => $flow->id,
                'division_operation_id' => $divOp->id,
                'status' => 'pending'
            ]);
        }
        
        // Update batch current position
        $batch->update([
            'current_division_id' => $division->id,
            'overall_status' => 'in_progress'
        ]);
        
        return $flow;
    }
    
    public function completeDivisionFlow(BatchFlow $flow, $outputQty, $rejectQty)
    {
        // Hitung yield
        $yield = ($outputQty / $flow->input_qty) * 100;
        
        // Update flow
        $flow->update([
            'status' => 'completed',
            'output_qty' => $outputQty,
            'reject_qty' => $rejectQty,
            'yield' => $yield,
            'completion_date' => now(),
            'completed_by' => Auth::user()->employee_id
        ]);
        
        // Update division yields di batch
        $batch = $flow->batch;
        $yields = $batch->division_yields ?? [];
        $yields[$flow->toDivision->division_code] = $yield;
        $batch->division_yields = $yields;
        
        // Cek jika ada divisi berikutnya
        $nextDivision = Division::where('sequence', '>', $flow->toDivision->sequence)
            ->orderBy('sequence')
            ->first();
            
        if ($nextDivision) {
            // Auto create flow untuk divisi berikutnya
            $this->startBatchInDivision($batch, $nextDivision);
        } else {
            // Batch completed
            $batch->update([
                'overall_status' => 'completed',
                'actual_finish' => now()
            ]);
        }
        
        return true;
    }
}