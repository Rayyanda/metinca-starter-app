<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Division;
use App\Models\Machine;
use App\Models\MachineOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{
    /**
     * Display a listing of operations
     */
    public function index(Request $request)
    {
        $query = Operation::with(['division', 'machines']);

        // Filter by division
        if ($request->has('division_id') && $request->division_id != '') {
            $query->where('division_id', $request->division_id);
        }

        // Filter by QC requirements
        if ($request->has('requires_qc')) {
            if ($request->requires_qc == 'before') {
                $query->where('requires_qc_before', true);
            } elseif ($request->requires_qc == 'after') {
                $query->where('requires_qc_after', true);
            }
        }

        // Filter by active status
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        $operations = $query->orderBy('sequence_order')->paginate(20);

        // Statistics
        $stats = [
            'total' => Operation::count(),
            'active' => Operation::active()->count(),
            'requires_qc' => Operation::where('requires_qc_before', true)
                ->orWhere('requires_qc_after', true)
                ->count(),
            'divisions' => Division::has('operations')->count(),
        ];

        // Get all divisions for filter
        $divisions = Division::active()->orderBy('name')->get();

        return view('operations.index', compact('operations', 'stats', 'divisions'));
    }

    /**
     * Show the form for creating a new operation
     */
    public function create()
    {
        $divisions = Division::active()->orderBy('name')->get();
        
        return view('operations.create', compact('divisions'));
    }

    /**
     * Store a newly created operation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:operations,code',
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'sequence_order' => 'required|integer|min:1',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'requires_qc_before' => 'sometimes|boolean',
            'requires_qc_after' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $operation = Operation::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'sequence_order' => $validated['sequence_order'],
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'requires_qc_before' => $request->has('requires_qc_before'),
            'requires_qc_after' => $request->has('requires_qc_after'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('operations.show', $operation->id)
            ->with('success', 'Operation created successfully!');
    }

    /**
     * Display the specified operation
     */
    public function show($id)
    {
        $operation = Operation::with([
            'division',
            'machines.division',
            'previousOperations',
            'nextOperations',
            'poOperations',
            'batchOperations'
        ])->findOrFail($id);

        // Get available machines (not yet assigned to this operation)
        $assignedMachineIds = $operation->machines->pluck('id')->toArray();
        $availableMachines = Machine::with('division')
            ->active()
            ->whereNotIn('id', $assignedMachineIds)
            ->orderBy('name')
            ->get();

        return view('operations.show', compact('operation', 'availableMachines'));
    }

    /**
     * Show the form for editing the specified operation
     */
    public function edit($id)
    {
        $operation = Operation::findOrFail($id);
        $divisions = Division::active()->orderBy('name')->get();

        return view('operations.edit', compact('operation', 'divisions'));
    }

    /**
     * Update the specified operation
     */
    public function update(Request $request, $id)
    {
        $operation = Operation::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:operations,code,' . $id,
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'sequence_order' => 'required|integer|min:1',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'requires_qc_before' => 'sometimes|boolean',
            'requires_qc_after' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $operation->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'sequence_order' => $validated['sequence_order'],
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'requires_qc_before' => $request->has('requires_qc_before'),
            'requires_qc_after' => $request->has('requires_qc_after'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('operations.show', $operation->id)
            ->with('success', 'Operation updated successfully!');
    }

    /**
     * Remove the specified operation
     */
    public function destroy($id)
    {
        $operation = Operation::findOrFail($id);

        // Check if operation is being used
        if ($operation->poOperations()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete operation that is being used in PO Internals.');
        }

        if ($operation->batchOperations()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete operation that is being used in batches.');
        }

        // Delete machine relationships first
        $operation->machines()->detach();

        // Delete operation flows
        DB::table('operation_flows')
            ->where('from_operation_id', $operation->id)
            ->orWhere('to_operation_id', $operation->id)
            ->delete();

        $operation->delete();

        return redirect()->route('operations.index')
            ->with('success', 'Operation deleted successfully!');
    }

    /**
     * Add machine to operation
     */
    public function addMachine(Request $request, $id)
    {
        $operation = Operation::findOrFail($id);

        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'setup_time_minutes' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_default' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        // Check if machine is already assigned
        if ($operation->machines()->where('machine_id', $validated['machine_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'Machine is already assigned to this operation.');
        }

        // If this is set as default, unset other defaults
        if ($request->has('is_default')) {
            $operation->machines()->updateExistingPivot(
                $operation->machines->pluck('id')->toArray(),
                ['is_default' => false]
            );
        }

        // Attach machine to operation
        $operation->machines()->attach($validated['machine_id'], [
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'setup_time_minutes' => $validated['setup_time_minutes'] ?? 0,
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'is_default' => $request->has('is_default'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('operations.show', $operation->id)
            ->with('success', 'Machine added to operation successfully!');
    }

    /**
     * Remove machine from operation
     */
    public function removeMachine($operationId, $machineId)
    {
        $operation = Operation::findOrFail($operationId);

        // Check if machine is being used in active batch operations
        $inUse = DB::table('batch_operations')
            ->where('operation_id', $operationId)
            ->where('machine_id', $machineId)
            ->whereIn('status', ['in_progress', 'qc_pending'])
            ->exists();

        if ($inUse) {
            return redirect()->back()
                ->with('error', 'Cannot remove machine that is currently being used in operations.');
        }

        $operation->machines()->detach($machineId);

        return redirect()->route('operations.show', $operation->id)
            ->with('success', 'Machine removed from operation successfully!');
    }

    /**
     * Update machine settings for operation
     */
    public function updateMachine(Request $request, $operationId, $machineId)
    {
        $operation = Operation::findOrFail($operationId);

        $validated = $request->validate([
            'estimated_duration_minutes' => 'required|integer|min:1',
            'setup_time_minutes' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_default' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($request->has('is_default')) {
            $operation->machines()->updateExistingPivot(
                $operation->machines->pluck('id')->toArray(),
                ['is_default' => false]
            );
        }

        $operation->machines()->updateExistingPivot($machineId, [
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'setup_time_minutes' => $validated['setup_time_minutes'] ?? 0,
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'is_default' => $request->has('is_default'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('operations.show', $operation->id)
            ->with('success', 'Machine settings updated successfully!');
    }

    /**
     * Get operations by division (API endpoint for dynamic loading)
     */
    public function getByDivision($divisionId)
    {
        $operations = Operation::where('division_id', $divisionId)
            ->active()
            ->orderBy('sequence_order')
            ->get(['id', 'name', 'code', 'estimated_duration_minutes']);

        return response()->json($operations);
    }

    /**
     * Get available machines for operation (API endpoint)
     */
    public function getAvailableMachines($operationId)
    {
        $operation = Operation::findOrFail($operationId);
        
        $machines = $operation->getAvailableMachines();

        return response()->json($machines);
    }

    /**
     * Toggle operation active status
     */
    public function toggleActive($id)
    {
        $operation = Operation::findOrFail($id);
        
        $operation->update([
            'is_active' => !$operation->is_active
        ]);

        $status = $operation->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Operation {$status} successfully!");
    }

    /**
     * Duplicate operation
     */
    public function duplicate($id)
    {
        $original = Operation::with('machines')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Create new operation
            $newOperation = Operation::create([
                'code' => $original->code . '-COPY',
                'name' => $original->name . ' (Copy)',
                'division_id' => $original->division_id,
                'sequence_order' => $original->sequence_order + 1,
                'estimated_duration_minutes' => $original->estimated_duration_minutes,
                'requires_qc_before' => $original->requires_qc_before,
                'requires_qc_after' => $original->requires_qc_after,
                'is_active' => false, // Start as inactive
            ]);

            // Copy machine relationships
            foreach ($original->machines as $machine) {
                $newOperation->machines()->attach($machine->id, [
                    'estimated_duration_minutes' => $machine->pivot->estimated_duration_minutes,
                    'setup_time_minutes' => $machine->pivot->setup_time_minutes,
                    'hourly_rate' => $machine->pivot->hourly_rate,
                    'is_default' => false, // Don't copy default status
                    'is_active' => $machine->pivot->is_active,
                ]);
            }

            DB::commit();

            return redirect()->route('operations.edit', $newOperation->id)
                ->with('success', 'Operation duplicated successfully! Please update the code and name.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to duplicate operation: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update sequence order
     */
    public function updateSequence(Request $request)
    {
        $validated = $request->validate([
            'operations' => 'required|array',
            'operations.*.id' => 'required|exists:operations,id',
            'operations.*.sequence_order' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['operations'] as $opData) {
                Operation::where('id', $opData['id'])
                    ->update(['sequence_order' => $opData['sequence_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sequence updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sequence: ' . $e->getMessage()
            ], 500);
        }
    }
}