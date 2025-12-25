<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\POInternal;
use App\Models\POOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POInternalController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = POInternal::with(['creator', 'operations.operation']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $poInternals = $query->latest()->paginate(15);
        return view('po-internals.index',compact('poInternals'));
        //return response()->json($poInternals);
    }

    public function create()
    {
        $operations = Operation::all();
        return view('po-internals.create',compact('operations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|unique:po_internals',
            'customer_name' => 'required|string',
            'product_description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'due_date' => 'nullable|date',
            'operations' => 'required|array|min:1',
            'operations.*.operation_id' => 'required|exists:operations,id',
            'operations.*.estimated_duration_minutes' => 'nullable|integer|min:0',
            'operations.*.sequence_order' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            // Create PO Internal
            $poInternal = POInternal::create([
                'po_number' => $validated['po_number'],
                'customer_name' => $validated['customer_name'],
                'product_description' => $validated['product_description'] ?? null,
                'quantity' => $validated['quantity'],
                'due_date' => $validated['due_date'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::user()->id,
            ]);

            // Create PO Operations
            foreach ($validated['operations'] as $op) {
                POOperation::create([
                    'po_internal_id' => $poInternal->id,
                    'operation_id' => $op['operation_id'],
                    'estimated_duration_minutes' => $op['estimated_duration_minutes'] ?? null,
                    'sequence_order' => $op['sequence_order'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'PO Internal created successfully',
                'data' => $poInternal->load('operations.operation'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create PO Internal', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $poInternal = POInternal::with([
            'creator',
            'operations.operation.division',
            'batches.operations'
        ])->findOrFail($id);

        return view('po-internals.show', compact('poInternal'));
    }

    public function edit($id)
    {
        $poInternal = POInternal::with(['operations.operation', 'batches'])->findOrFail($id);

        if ($poInternal->status !== 'draft') {
            return redirect()->route('po-internals.show', $poInternal->id)
                ->with('error', 'Only draft PO can be edited.');
        }

        return view('po-internals.edit', compact('poInternal'));
    }

    public function update(Request $request, $id)
    {
        $poInternal = POInternal::with('batches')->findOrFail($id);

        $validated = $request->validate([
            'po_number' => 'required|string|unique:po_internals,po_number,' . $id,
            'customer_name' => 'required|string',
            'product_description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:draft,confirmed,in_production,completed,cancelled',
        ]);

        // Validate quantity tidak kurang dari yang sudah di-batch
        $batchedQuantity = $poInternal->batches->sum('quantity');
        if ($validated['quantity'] < $batchedQuantity) {
            return redirect()->back()
                ->with('error', "Quantity cannot be less than batched quantity ({$batchedQuantity} pcs)")
                ->withInput();
        }

        $poInternal->update($validated);

        return redirect()->route('po-internals.show', $poInternal->id)
            ->with('success', 'PO Internal updated successfully!');
    }

    public function destroy($id)
    {
        $poInternal = POInternal::findOrFail($id);

        if ($poInternal->batches()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete PO Internal with existing batches');
        }

        $poInternal->delete();

        return redirect()->route('po-internals.index')
            ->with('success', 'PO Internal deleted successfully!');
    }

    /**
     * Confirm PO (change status from draft to confirmed)
     */
    public function confirm($id)
    {
        $poInternal = POInternal::findOrFail($id);

        if ($poInternal->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Only draft PO can be confirmed.');
        }

        // Validate that PO has operations
        if ($poInternal->operations()->count() == 0) {
            return redirect()->back()
                ->with('error', 'PO must have at least one operation before confirmation.');
        }

        $poInternal->update(['status' => 'confirmed']);

        return redirect()->route('po-internals.show', $poInternal->id)
            ->with('success', 'PO confirmed successfully! You can now create batches.');
    }
}
