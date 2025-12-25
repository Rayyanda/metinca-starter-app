<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Division;
use App\Models\MachineDowntime;
use App\Models\BatchOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MachineController extends Controller
{
    /**
     * Display a listing of machines
     */
    public function index(Request $request)
    {
        $query = Machine::with(['division', 'downtimes' => function($q) {
            $q->ongoing()->latest();
        }]);

        // Filter by division
        if ($request->has('division_id') && $request->division_id != '') {
            $query->where('division_id', $request->division_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Only active
        if ($request->has('active') && $request->active) {
            $query->active();
        }

        $machines = $query->orderBy('division_id')->orderBy('name')->paginate(20);

        // Statistics
        $stats = [
            'total' => Machine::count(),
            'available' => Machine::available()->count(),
            'in_use' => Machine::inUse()->count(),
            'issues' => Machine::whereIn('status', ['maintenance', 'breakdown'])->count(),
        ];

        // Get divisions for filter
        $divisions = Division::active()->orderBy('name')->get();

        return view('machines.index', compact('machines', 'stats', 'divisions'));
    }

    /**
     * Show the form for creating a new machine
     */
    public function create()
    {
        $divisions = Division::active()->orderBy('name')->get();
        
        return view('machines.create', compact('divisions'));
    }

    /**
     * Store a newly created machine
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:machines,code',
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'machine_type' => 'nullable|string|max:100',
            'status' => 'sometimes|in:available,in_use,maintenance,breakdown',
            'max_concurrent_operations' => 'required|integer|min:1',
            'specifications' => 'nullable|json',
            'is_active' => 'sometimes|boolean',
        ]);

        // Parse specifications if provided
        if (isset($validated['specifications'])) {
            try {
                $validated['specifications'] = json_decode($validated['specifications'], true);
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Invalid JSON format for specifications')
                    ->withInput();
            }
        }

        $machine = Machine::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'machine_type' => $validated['machine_type'] ?? null,
            'status' => $validated['status'] ?? 'available',
            'max_concurrent_operations' => $validated['max_concurrent_operations'],
            'current_operations' => 0,
            'specifications' => $validated['specifications'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('machines.show', $machine->id)
            ->with('success', 'Machine created successfully!');
    }

    /**
     * Display the specified machine
     */
    public function show($id)
    {
        $machine = Machine::with([
            'division',
            'operations',
            'downtimes' => function($q) {
                $q->latest('started_at');
            },
            'downtimes.reporter',
            'downtimes.resolver',
            'batchOperations'
        ])->findOrFail($id);

        return view('machines.show', compact('machine'));
    }

    /**
     * Show the form for editing the specified machine
     */
    public function edit($id)
    {
        $machine = Machine::findOrFail($id);
        $divisions = Division::active()->orderBy('name')->get();

        return view('machines.edit', compact('machine', 'divisions'));
    }

    /**
     * Update the specified machine
     */
    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:machines,code,' . $id,
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'machine_type' => 'nullable|string|max:100',
            'status' => 'sometimes|in:available,in_use,maintenance,breakdown',
            'max_concurrent_operations' => 'required|integer|min:1',
            'specifications' => 'nullable|json',
            'is_active' => 'sometimes|boolean',
        ]);

        // Parse specifications if provided
        if (isset($validated['specifications'])) {
            try {
                $validated['specifications'] = json_decode($validated['specifications'], true);
            } catch (\Exception $e) {
                return redirect()->back()
                    ->with('error', 'Invalid JSON format for specifications')
                    ->withInput();
            }
        }

        // Check if reducing max_concurrent_operations below current_operations
        if ($validated['max_concurrent_operations'] < $machine->current_operations) {
            return redirect()->back()
                ->with('error', 'Cannot set max operations below current running operations')
                ->withInput();
        }

        $machine->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'machine_type' => $validated['machine_type'] ?? null,
            'status' => $validated['status'] ?? $machine->status,
            'max_concurrent_operations' => $validated['max_concurrent_operations'],
            'specifications' => $validated['specifications'] ?? $machine->specifications,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('machines.show', $machine->id)
            ->with('success', 'Machine updated successfully!');
    }

    /**
     * Remove the specified machine
     */
    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);

        // Check if machine is in use
        if ($machine->current_operations > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete machine that has ongoing operations');
        }

        // Check if machine is assigned to operations
        if ($machine->operations()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete machine that is assigned to operations. Remove assignments first.');
        }

        // Check if machine has been used in batch operations
        if ($machine->batchOperations()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete machine that has historical batch operations');
        }

        $machine->delete();

        return redirect()->route('machines.index')
            ->with('success', 'Machine deleted successfully!');
    }

    /**
     * Report machine downtime
     */
    public function reportDowntime(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        $validated = $request->validate([
            'downtime_type' => 'required|in:maintenance,breakdown,calibration',
            'reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Create downtime record
            $downtime = MachineDowntime::create([
                'machine_id' => $machine->id,
                'downtime_type' => $validated['downtime_type'],
                'started_at' => now(),
                'reason' => $validated['reason'],
                'reported_by' => Auth::user()->id,
            ]);

            // Update machine status
            $status = $validated['downtime_type'] === 'breakdown' ? 'breakdown' : 'maintenance';
            $machine->update(['status' => $status]);

            // Pause all operations on this machine
            $pausedCount = $machine->batchOperations()
                ->inProgress()
                ->update([
                    'status' => 'on_hold',
                    'paused_at' => now(),
                    'paused_reason' => 'machine_' . $validated['downtime_type'],
                ]);

            DB::commit();

            $message = "Machine downtime reported successfully!";
            if ($pausedCount > 0) {
                $message .= " {$pausedCount} operation(s) have been paused.";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to report downtime: ' . $e->getMessage());
        }
    }

    /**
     * Resolve machine downtime
     */
    public function resolveDowntime(Request $request, $downtimeId)
    {
        $downtime = MachineDowntime::with('machine')->findOrFail($downtimeId);

        if ($downtime->ended_at) {
            return redirect()->back()
                ->with('error', 'Downtime already resolved');
        }

        $validated = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Update downtime record
            $downtime->update([
                'ended_at' => now(),
                'resolution_notes' => $validated['resolution_notes'],
                'resolved_by' => Auth::user()->id,
            ]);

            // Update machine status to available
            $downtime->machine->update(['status' => 'available']);

            // Note: Paused operations are NOT auto-resumed
            // They need manual resume by operators

            DB::commit();

            return redirect()->back()
                ->with('success', 'Machine downtime resolved successfully! Machine is now available.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to resolve downtime: ' . $e->getMessage());
        }
    }

    /**
     * Get available machines for operation (API endpoint)
     */
    public function getAvailableForOperation($operationId)
    {
        $machines = Machine::whereHas('operations', function($q) use ($operationId) {
            $q->where('operation_id', $operationId)
              ->where('machine_operations.is_active', true);
        })
        ->active()
        ->available()
        ->with(['operations' => function($q) use ($operationId) {
            $q->where('operation_id', $operationId);
        }])
        ->get()
        ->map(function($machine) {
            $machineOp = $machine->operations->first();
            return [
                'id' => $machine->id,
                'name' => $machine->name,
                'code' => $machine->code,
                'status' => $machine->status,
                'current_operations' => $machine->current_operations,
                'max_concurrent_operations' => $machine->max_concurrent_operations,
                'estimated_duration_minutes' => $machineOp->pivot->estimated_duration_minutes ?? 0,
                'setup_time_minutes' => $machineOp->pivot->setup_time_minutes ?? 0,
                'is_default' => $machineOp->pivot->is_default ?? false,
            ];
        });

        return response()->json($machines);
    }

    /**
     * Toggle machine active status
     */
    public function toggleActive($id)
    {
        $machine = Machine::findOrFail($id);
        
        // Check if machine has ongoing operations
        if (!$machine->is_active && $machine->current_operations > 0) {
            return redirect()->back()
                ->with('error', 'Cannot deactivate machine with ongoing operations');
        }

        $machine->update([
            'is_active' => !$machine->is_active
        ]);

        $status = $machine->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Machine {$status} successfully!");
    }

    /**
     * Get machine statistics (API endpoint)
     */
    public function getStatistics($id)
    {
        $machine = Machine::with(['batchOperations', 'downtimes'])->findOrFail($id);

        // Last 30 days
        $startDate = now()->subDays(30);

        $stats = [
            'total_operations' => $machine->batchOperations()
                ->where('created_at', '>=', $startDate)
                ->count(),
            'completed_operations' => $machine->batchOperations()
                ->completed()
                ->where('actual_completion_at', '>=', $startDate)
                ->count(),
            'total_downtime_hours' => $machine->downtimes()
                ->where('started_at', '>=', $startDate)
                ->get()
                ->sum(function($d) {
                    return $d->getDurationMinutes();
                }) / 60,
            'breakdown_count' => $machine->downtimes()
                ->where('downtime_type', 'breakdown')
                ->where('started_at', '>=', $startDate)
                ->count(),
            'maintenance_count' => $machine->downtimes()
                ->where('downtime_type', 'maintenance')
                ->where('started_at', '>=', $startDate)
                ->count(),
        ];

        // Calculate utilization rate
        $totalMinutes = 30 * 24 * 60; // 30 days in minutes
        $downtimeMinutes = $stats['total_downtime_hours'] * 60;
        $productiveMinutes = $machine->batchOperations()
            ->completed()
            ->where('actual_completion_at', '>=', $startDate)
            ->get()
            ->sum(function($op) {
                return $op->getActualDurationMinutes() ?? 0;
            });

        $stats['utilization_rate'] = $totalMinutes > 0 
            ? round(($productiveMinutes / $totalMinutes) * 100, 2) 
            : 0;

        return response()->json($stats);
    }

    /**
     * Get machine downtime history
     */
    public function downtimeHistory(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);

        $query = $machine->downtimes()
            ->with(['reporter', 'resolver'])
            ->orderBy('started_at', 'desc');

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('downtime_type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('started_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('started_at', '<=', $request->end_date);
        }

        $downtimes = $query->paginate(20);

        return view('machines.downtime-history', compact('machine', 'downtimes'));
    }

    /**
     * Bulk update machine status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'machine_ids' => 'required|array',
            'machine_ids.*' => 'exists:machines,id',
            'status' => 'required|in:available,in_use,maintenance,breakdown',
        ]);

        $updated = Machine::whereIn('id', $validated['machine_ids'])
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} machine(s) updated successfully!",
        ]);
    }

    /**
     * Export machines data
     */
    public function export(Request $request)
    {
        $machines = Machine::with(['division', 'operations'])
            ->when($request->has('division_id'), function($q) use ($request) {
                $q->where('division_id', $request->division_id);
            })
            ->when($request->has('status'), function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->get();

        // Create CSV
        $filename = 'machines-' . date('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($machines) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Code',
                'Name',
                'Division',
                'Type',
                'Status',
                'Max Operations',
                'Current Operations',
                'Assigned Operations',
                'Active'
            ]);

            // Data
            foreach ($machines as $machine) {
                fputcsv($file, [
                    $machine->code,
                    $machine->name,
                    $machine->division->name,
                    $machine->machine_type ?? '',
                    $machine->status,
                    $machine->max_concurrent_operations,
                    $machine->current_operations,
                    $machine->operations->count(),
                    $machine->is_active ? 'Yes' : 'No',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}