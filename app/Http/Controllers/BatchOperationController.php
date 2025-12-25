<?php

namespace App\Http\Controllers;

use App\Models\BatchOperation;
use App\Models\Machine;
use App\Models\OperationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchOperationController extends Controller
{
    // Get operations for operator dashboard (by division)
    public function myOperations(Request $request)
    {
        $user = auth()->user();
        
        $query = BatchOperation::with([
            'batch.poInternal',
            'operation.division',
            'machine',
            'operator'
        ])->byDivision($user->division_id);

        // Filter by status
        $status = $request->get('status', 'ready');
        
        if ($status == 'completed') {
            $query->completed()->whereDate('actual_completion_at', '>=', now()->subDays(7));
        } else {
            $query->where('status', $status);
        }

        $operations = $query->orderBy('batch_id')
            ->orderBy('sequence_order')
            ->paginate(20);

        // Stats
        $myActiveOperations = BatchOperation::inProgress()
            ->where('operator_id', $user->id)
            ->count();
            
        $readyOperations = BatchOperation::ready()
            ->byDivision($user->division_id)
            ->count();
            
        $completedToday = BatchOperation::completed()
            ->where('operator_id', $user->id)
            ->whereDate('actual_completion_at', today())
            ->count();

        return view('operations.index', compact(
            'operations',
            'myActiveOperations',
            'readyOperations',
            'completedToday'
        ));
    }

    // Show start operation form
    public function showStartForm($id)
    {
        $batchOperation = BatchOperation::with([
            'batch.poInternal',
            'batch.operations',
            'operation.division'
        ])->findOrFail($id);

        if (!$batchOperation->canStart()) {
            return redirect()->route('operations.my')
                ->with('error', 'This operation cannot be started at this time.');
        }

        // Get available machines for this operation
        $availableMachines = Machine::whereHas('operations', function($q) use ($batchOperation) {
            $q->where('operation_id', $batchOperation->operation_id)
              ->where('machine_operations.is_active', true);
        })
        ->with(['operations' => function($q) use ($batchOperation) {
            $q->where('operation_id', $batchOperation->operation_id);
        }])
        ->active()
        ->whereIn('status', ['available', 'in_use'])
        ->get();

        return view('operations.start', compact('batchOperation', 'availableMachines'));
    }

    public function start(Request $request, $id)
    {
        $batchOperation = BatchOperation::with(['batch', 'operation'])->findOrFail($id);

        if (!$batchOperation->canStart()) {
            return redirect()->route('operations.my')
                ->with('error', 'Operation cannot be started');
        }

        // Check if QC required before start
        if ($batchOperation->requiresQCBefore()) {
            return redirect()->route('operations.my')
                ->with('error', 'Quality check required before starting');
        }

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);

        // Check if machine is available
        if (!$machine->canAcceptOperation()) {
            return redirect()->back()
                ->with('error', 'Machine is not available');
        }

        // Check if machine can do this operation
        $machineOp = $machine->operations()->where('operation_id', $batchOperation->operation_id)->first();
        if (!$machineOp) {
            return redirect()->back()
                ->with('error', 'Machine cannot perform this operation');
        }

        DB::beginTransaction();
        try {
            // Get duration from machine_operations
            $duration = $machineOp->pivot->estimated_duration_minutes;
            $setupTime = $machineOp->pivot->setup_time_minutes ?? 0;
            $totalDuration = $duration + $setupTime;

            // Update batch operation
            $batchOperation->update([
                'status' => 'in_progress',
                'machine_id' => $machine->id,
                'operator_id' => auth()->id(),
                'actual_start_at' => now(),
                'estimated_completion_at' => now()->addMinutes($totalDuration),
            ]);

            // Update machine
            $machine->incrementOperations();

            // Update batch status
            $batchOperation->batch->update([
                'status' => 'in_progress',
                'current_operation_id' => $batchOperation->operation_id,
            ]);

            // Log history
            OperationHistory::log(
                $batchOperation->id,
                'started',
                'ready',
                'in_progress',
                auth()->id(),
                'Operation started on machine: ' . $machine->name,
                ['machine_id' => $machine->id]
            );

            DB::commit();

            return redirect()->route('operations.my', ['status' => 'in_progress'])
                ->with('success', 'Operation started successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to start operation: ' . $e->getMessage());
        }
    }

    // Show complete operation form
    public function showCompleteForm($id)
    {
        $batchOperation = BatchOperation::with([
            'batch.poInternal',
            'operation',
            'machine',
            'operator'
        ])->findOrFail($id);

        if ($batchOperation->status !== 'in_progress') {
            return redirect()->route('operations.my')
                ->with('error', 'This operation is not in progress.');
        }

        return view('operations.complete', compact('batchOperation'));
    }

    public function complete(Request $request, $id)
    {
        $batchOperation = BatchOperation::with(['batch', 'operation', 'machine'])->findOrFail($id);

        if ($batchOperation->status !== 'in_progress') {
            return redirect()->route('operations.my')
                ->with('error', 'Operation is not in progress');
        }

        $validated = $request->validate([
            'actual_good_quantity' => 'required|integer|min:0',
            'actual_reject_quantity' => 'required|integer|min:0',
            'operator_notes' => 'nullable|string',
        ]);

        // Validate total quantity
        $total = $validated['actual_good_quantity'] + $validated['actual_reject_quantity'];
        if ($total != $batchOperation->batch->quantity) {
            return redirect()->back()
                ->with('error', "Total quantity must equal {$batchOperation->batch->quantity} pcs")
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update batch operation
            $batchOperation->update([
                'status' => $batchOperation->requiresQCAfter() ? 'qc_pending' : 'completed',
                'actual_completion_at' => now(),
                'actual_good_quantity' => $validated['actual_good_quantity'],
                'actual_reject_quantity' => $validated['actual_reject_quantity'],
                'operator_notes' => $validated['operator_notes'] ?? null,
            ]);

            // Release machine
            if ($batchOperation->machine) {
                $batchOperation->machine->decrementOperations();
            }

            // Log history
            OperationHistory::log(
                $batchOperation->id,
                'completed',
                'in_progress',
                $batchOperation->status,
                auth()->id(),
                'Operation completed'
            );

            // If no QC required and this is last operation, mark batch as completed
            if (!$batchOperation->requiresQCAfter()) {
                $nextOp = $batchOperation->batch->getNextOperation();
                if (!$nextOp) {
                    // This was the last operation
                    $batchOperation->batch->update([
                        'status' => 'completed',
                        'actual_completion_at' => now(),
                    ]);
                } else {
                    // Set next operation as ready
                    $nextOp->update(['status' => 'ready']);
                }
            }

            DB::commit();

            $message = $batchOperation->requiresQCAfter() 
                ? 'Operation completed! Sent to QC for inspection.'
                : 'Operation completed successfully!';

            return redirect()->route('operations.my', ['status' => 'ready'])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to complete operation: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function pause(Request $request, $id)
    {
        $batchOperation = BatchOperation::findOrFail($id);

        if ($batchOperation->status !== 'in_progress') {
            return redirect()->route('operations.my')
                ->with('error', 'Operation is not in progress');
        }

        $validated = $request->validate([
            'paused_reason' => 'required|string',
        ]);

        $batchOperation->update([
            'status' => 'on_hold',
            'paused_at' => now(),
            'paused_reason' => $validated['paused_reason'],
        ]);

        // Log history
        OperationHistory::log(
            $batchOperation->id,
            'paused',
            'in_progress',
            'on_hold',
            auth()->id(),
            $validated['paused_reason']
        );

        return redirect()->route('operations.my')
            ->with('success', 'Operation paused successfully.');
    }

    public function resume($id)
    {
        $batchOperation = BatchOperation::findOrFail($id);

        if ($batchOperation->status !== 'on_hold') {
            return redirect()->route('operations.my')
                ->with('error', 'Operation is not on hold');
        }

        $batchOperation->update([
            'status' => 'in_progress',
            'resumed_at' => now(),
        ]);

        // Log history
        OperationHistory::log(
            $batchOperation->id,
            'resumed',
            'on_hold',
            'in_progress',
            auth()->id(),
            'Operation resumed'
        );

        return redirect()->route('operations.my', ['status' => 'in_progress'])
            ->with('success', 'Operation resumed successfully.');
    }
}