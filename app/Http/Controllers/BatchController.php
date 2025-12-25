<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchOperation;
use App\Models\POInternal;
use App\Models\BatchApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with(['poInternal', 'creator', 'currentOperation']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority != '') {
            $query->where('priority', $request->priority);
        }

        // Filter rush orders
        if ($request->has('rush_order') && $request->rush_order == '1') {
            $query->where('is_rush_order', true);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('poInternal', function($q2) use ($search) {
                      $q2->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        // Order by priority
        $batches = $query->orderByPriority()->paginate(20);

        // Statistics
        $stats = [
            'total' => Batch::count(),
            'in_progress' => Batch::inProgress()->count(),
            'rush' => Batch::rushOrder()->whereIn('status', ['released', 'in_progress'])->count(),
            'completed' => Batch::completed()->count(),
        ];

        return view('batches.index', compact('batches', 'stats'));
    }

    public function create()
    {
        // Get PO Internals that are confirmed and not fully batched
        $poInternals = POInternal::with(['operations.operation.division'])
            ->whereIn('status', ['confirmed', 'in_production'])
            ->get()
            ->filter(function($po) {
                return $po->getRemainingQuantity() > 0;
            });

        return view('batches.create', compact('poInternals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_internal_id' => 'required|exists:po_internals,id',
            'quantity' => 'required|integer|min:1',
            'priority' => 'sometimes|integer|in:1,2,3',
            'is_rush_order' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        // Check remaining quantity
        $poInternal = POInternal::findOrFail($validated['po_internal_id']);
        $remaining = $poInternal->getRemainingQuantity();

        if ($validated['quantity'] > $remaining) {
            return redirect()->back()
                ->with('error', "Quantity exceeds remaining PO quantity. Remaining: {$remaining}")
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Generate batch number
            $today = date('Ymd');
            $count = Batch::whereDate('created_at', today())->count() + 1;
            $batchNumber = 'BATCH-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Create batch
            $batch = Batch::create([
                'batch_number' => $batchNumber,
                'po_internal_id' => $validated['po_internal_id'],
                'quantity' => $validated['quantity'],
                'priority' => $validated['priority'] ?? 1,
                'is_rush_order' => $request->has('is_rush_order'),
                'status' => 'draft',
                'created_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create batch operations from PO operations
            $poOperations = $poInternal->operations()->orderBy('sequence_order')->get();
            $estimatedStart = now();

            foreach ($poOperations as $index => $poOp) {
                $estimatedDuration = $poOp->estimated_duration_minutes ?? $poOp->operation->estimated_duration_minutes;
                
                BatchOperation::create([
                    'batch_id' => $batch->id,
                    'operation_id' => $poOp->operation_id,
                    'sequence_order' => $poOp->sequence_order,
                    'estimated_duration_minutes' => $estimatedDuration,
                    'status' => $index === 0 ? 'pending' : 'pending', // First will be 'ready' after approval
                    'estimated_start_at' => $estimatedStart,
                    'estimated_completion_at' => $estimatedStart->copy()->addMinutes($estimatedDuration),
                ]);

                $estimatedStart->addMinutes($estimatedDuration);
            }

            // Update batch estimated completion
            $batch->update(['estimated_completion_at' => $estimatedStart]);

            // Update PO status
            if ($poInternal->status !== 'in_production') {
                $poInternal->update(['status' => 'in_production']);
            }

            DB::commit();

            return redirect()->route('batches.show', $batch->id)
                ->with('success', 'Batch created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create batch: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $batch = Batch::with([
            'poInternal',
            'creator',
            'operations.operation.division',
            'operations.machine',
            'operations.operator',
            'operations.histories.user',
            'approvals.approver',
            'currentOperation'
        ])->findOrFail($id);

        return view('batches.show', compact('batch'));
    }

    public function edit($id)
    {
        $batch = Batch::with(['poInternal', 'operations'])->findOrFail($id);

        if (!in_array($batch->status, ['draft', 'rejected'])) {
            return redirect()->route('batches.show', $batch->id)
                ->with('error', 'Only draft or rejected batches can be edited.');
        }

        return view('batches.edit', compact('batch'));
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        if (!in_array($batch->status, ['draft', 'rejected'])) {
            return redirect()->route('batches.show', $batch->id)
                ->with('error', 'Only draft or rejected batches can be edited.');
        }

        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'priority' => 'sometimes|integer|in:1,2,3',
            'is_rush_order' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        // Check quantity doesn't exceed PO remaining
        if (isset($validated['quantity'])) {
            $poInternal = $batch->poInternal;
            $remaining = $poInternal->getRemainingQuantity() + $batch->quantity; // Add back current batch qty
            
            if ($validated['quantity'] > $remaining) {
                return redirect()->back()
                    ->with('error', "Quantity exceeds remaining PO quantity. Max: {$remaining}")
                    ->withInput();
            }
        }

        $batch->update([
            'quantity' => $validated['quantity'] ?? $batch->quantity,
            'priority' => $validated['priority'] ?? $batch->priority,
            'is_rush_order' => $request->has('is_rush_order'),
            'notes' => $validated['notes'] ?? $batch->notes,
        ]);

        return redirect()->route('batches.show', $batch->id)
            ->with('success', 'Batch updated successfully!');
    }

    public function submitForApproval($id)
    {
        $batch = Batch::with('operations.operation.division')->findOrFail($id);

        if ($batch->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Only draft batches can be submitted for approval');
        }

        DB::beginTransaction();
        try {
            // Update batch status
            $batch->update(['status' => 'pending_approval']);

            // Create approval request (find supervisor of first operation's division)
            $firstOperation = $batch->operations()->orderBy('sequence_order')->first();
            $supervisor = User::byRole('supervisor')
                ->byDivision($firstOperation->operation->division_id)
                ->first();

            if (!$supervisor) {
                throw new \Exception('No supervisor found for approval');
            }

            BatchApproval::create([
                'batch_id' => $batch->id,
                'approval_type' => 'release',
                'status' => 'pending',
                'approver_id' => $supervisor->id,
                'approval_level' => 1,
            ]);

            DB::commit();

            return redirect()->route('batches.show', $batch->id)
                ->with('success', 'Batch submitted for approval successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to submit batch: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $batch = Batch::findOrFail($id);

        if (!in_array($batch->status, ['draft', 'pending_approval'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete batch that is already in production');
        }

        $batch->delete();

        return redirect()->route('batches.index')
            ->with('success', 'Batch deleted successfully!');
    }
}