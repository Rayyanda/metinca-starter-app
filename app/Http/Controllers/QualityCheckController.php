<?php

namespace App\Http\Controllers;

use App\Models\QualityCheck;
use App\Models\QCRejection;
use App\Models\BatchOperation;
use App\Models\OperationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityCheckController extends Controller
{
    /**
     * Display pending QC operations (Dynamic per division)
     */
    public function pending(Request $request)
    {
        $user = auth()->user();

        // Check if user has division
        if (!$user->division_id) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to a division to perform QC.');
        }

        // Determine check type
        $checkType = $request->get('type', 'after_complete');

        // Build query based on check type
        $query = BatchOperation::with([
            'batch.poInternal',
            'operation',
            'machine',
            'operator'
        ])
        ->byDivision($user->division_id);

        if ($checkType == 'before_start') {
            // Operations that need QC before starting
            $query->where('status', 'ready')
                  ->whereHas('operation', function($q) {
                      $q->where('requires_qc_before', true);
                  });
        } else {
            // Operations that need QC after completion (default)
            $query->where('status', 'qc_pending');
        }

        $operations = $query->orderBy('actual_completion_at', 'asc')
            ->orderBy('batch_id')
            ->paginate(20);

        // Count pending by type
        $pendingBefore = BatchOperation::byDivision($user->division_id)
            ->where('status', 'ready')
            ->whereHas('operation', function($q) {
                $q->where('requires_qc_before', true);
            })
            ->count();

        $pendingAfter = BatchOperation::byDivision($user->division_id)
            ->where('status', 'qc_pending')
            ->count();

        // Statistics
        $stats = [
            'pending' => $pendingBefore + $pendingAfter,
            'passed_today' => BatchOperation::byDivision($user->division_id)
                ->where('status', 'qc_passed')
                ->whereHas('qualityChecks', function($q) {
                    $q->where('result', 'pass')
                      ->whereDate('checked_at', today());
                })
                ->count(),
            'failed_today' => BatchOperation::byDivision($user->division_id)
                ->where('status', 'qc_failed')
                ->whereHas('qualityChecks', function($q) {
                    $q->where('result', 'fail')
                      ->whereDate('checked_at', today());
                })
                ->count(),
        ];

        return view('qc.pending', compact(
            'operations',
            'stats',
            'pendingBefore',
            'pendingAfter',
            'checkType'
        ));
    }

    /**
     * Show QC check form
     */
    public function showCheckForm($batchOperationId)
    {
        $user = auth()->user();

        $batchOperation = BatchOperation::with([
            'batch.poInternal',
            'operation.division',
            'machine',
            'operator',
            'qualityChecks' => function($q) {
                $q->latest('checked_at');
            }
        ])->findOrFail($batchOperationId);

        // Check if operation belongs to user's division
        if ($batchOperation->operation->division_id != $user->division_id) {
            return redirect()->route('qc.pending')
                ->with('error', 'This operation does not belong to your division.');
        }

        // Determine check type
        $checkType = 'after_complete';
        if ($batchOperation->status == 'ready' && $batchOperation->operation->requires_qc_before) {
            $checkType = 'before_start';
        } elseif ($batchOperation->status == 'qc_pending') {
            $checkType = 'after_complete';
        } else {
            return redirect()->route('qc.pending')
                ->with('error', 'This operation does not need QC at this time.');
        }

        // Get previous QC (if any)
        $previousQC = $batchOperation->qualityChecks->first();

        return view('qc.check-form', compact('batchOperation', 'checkType', 'previousQC'));
    }

    /**
     * Submit QC check
     */
    public function submit(Request $request, $batchOperationId)
    {
        $user = auth()->user();

        $batchOperation = BatchOperation::with(['batch', 'operation'])->findOrFail($batchOperationId);

        // Check if operation belongs to user's division
        if ($batchOperation->operation->division_id != $user->division_id) {
            return redirect()->route('qc.pending')
                ->with('error', 'This operation does not belong to your division.');
        }

        // Validate status
        $validStatuses = ['qc_pending', 'ready'];
        if (!in_array($batchOperation->status, $validStatuses)) {
            return redirect()->route('qc.pending')
                ->with('error', 'Operation is not in a valid state for QC check.');
        }

        $validated = $request->validate([
            'check_type' => 'required|in:before_start,after_complete,in_process',
            'result' => 'required|in:pass,fail,conditional_pass',
            'checked_quantity' => 'required|integer|min:1',
            'passed_quantity' => 'required|integer|min:0',
            'failed_quantity' => 'required|integer|min:0',
            'defect_description' => 'required_if:result,fail,conditional_pass|nullable|string',
            'corrective_action' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Validate total quantity
        $total = $validated['passed_quantity'] + $validated['failed_quantity'];
        if ($total != $validated['checked_quantity']) {
            return redirect()->back()
                ->with('error', "Passed + Failed quantity must equal checked quantity ({$validated['checked_quantity']})")
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create quality check
            $qc = QualityCheck::create([
                'batch_operation_id' => $batchOperation->id,
                'check_type' => $validated['check_type'],
                'result' => $validated['result'],
                'checked_quantity' => $validated['checked_quantity'],
                'passed_quantity' => $validated['passed_quantity'],
                'failed_quantity' => $validated['failed_quantity'],
                'defect_description' => $validated['defect_description'] ?? null,
                'corrective_action' => $validated['corrective_action'] ?? null,
                'checked_by' => $user->id,
                'checked_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $previousStatus = $batchOperation->status;

            // Update batch operation status based on QC result and check type
            if ($validated['check_type'] == 'before_start') {
                // QC Before Start
                if ($qc->result === 'pass' || $qc->result === 'conditional_pass') {
                    $batchOperation->update(['status' => 'qc_passed']);
                    // Status tetap ready tapi sudah lulus QC, operator bisa mulai
                } else {
                    $batchOperation->update(['status' => 'qc_failed']);
                    // Tidak bisa dimulai sampai di-resolve
                }
            } else {
                // QC After Complete
                if ($qc->result === 'pass' || $qc->result === 'conditional_pass') {
                    $batchOperation->update(['status' => 'qc_passed']);

                    // Check if this is the last operation
                    $nextOp = $batchOperation->batch->getNextOperation();
                    if (!$nextOp) {
                        // Mark batch as completed
                        $batchOperation->batch->update([
                            'status' => 'completed',
                            'actual_completion_at' => now(),
                        ]);
                    } else {
                        // Set next operation as ready
                        $nextOp->update(['status' => 'ready']);
                    }
                } else {
                    $batchOperation->update(['status' => 'qc_failed']);
                }
            }

            // Log history
            OperationHistory::log(
                $batchOperation->id,
                'qc_checked',
                $previousStatus,
                $batchOperation->status,
                $user->id,
                "QC Result: {$qc->result} ({$qc->check_type})"
            );

            // If failed, create rejection record
            if ($qc->result === 'fail') {
                $this->createRejection($qc, $request);
            }

            DB::commit();

            $message = "Quality check submitted successfully! Result: " . ucfirst($qc->result);
            if ($qc->result === 'fail') {
                $message .= " - Rejection record created.";
            }

            return redirect()->route('qc.pending')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to submit QC: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Create rejection record for failed QC
     */
    private function createRejection($qc, $request)
    {
        // Find operator from same division for rework assignment
        $operator = \App\Models\User::byDivision($qc->batchOperation->operation->division_id)
            ->byRole('operator')
            ->where('is_active', true)
            ->first();

        QCRejection::create([
            'quality_check_id' => $qc->id,
            'batch_operation_id' => $qc->batch_operation_id,
            'rejected_quantity' => $qc->failed_quantity,
            'total_quantity' => $qc->checked_quantity,
            'reject_reason' => $qc->defect_description,
            'action_taken' => 'rework', // Default action
            'rework_assigned_to' => $operator?->id,
            'rework_deadline' => now()->addHours(24), // Default 24 hours
            'rework_status' => 'pending',
        ]);
    }

    /**
     * Display QC history (Dynamic per division)
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        if (!$user->division_id) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to a division.');
        }

        $query = QualityCheck::with([
            'batchOperation.batch',
            'batchOperation.operation',
            'checker'
        ])
        ->whereHas('batchOperation.operation', function($q) use ($user) {
            $q->where('division_id', $user->division_id);
        });

        // Filter by result
        if ($request->has('result') && $request->result != '') {
            $query->where('result', $request->result);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('checked_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('checked_at', '<=', $request->date_to);
        }

        // Only show current user's checks if not supervisor/admin
        if (!$user->isSupervisor() && !$user->isAdmin()) {
            $query->where('checked_by', $user->id);
        }

        $qualityChecks = $query->orderBy('checked_at', 'desc')->paginate(20);

        return view('qc.history', compact('qualityChecks'));
    }

    /**
     * Show QC detail
     */
    public function show($id)
    {
        $user = auth()->user();

        $qualityCheck = QualityCheck::with([
            'batchOperation.batch.poInternal',
            'batchOperation.operation.division',
            'batchOperation.machine',
            'batchOperation.operator',
            'checker',
            'rejection'
        ])->findOrFail($id);

        // Check if QC belongs to user's division
        if ($qualityCheck->batchOperation->operation->division_id != $user->division_id) {
            return redirect()->route('qc.pending')
                ->with('error', 'This QC record does not belong to your division.');
        }

        return view('qc.show', compact('qualityCheck'));
    }

    /**
     * Get QC statistics for division (API endpoint)
     */
    public function getStatistics(Request $request)
    {
        $user = auth()->user();

        if (!$user->division_id) {
            return response()->json(['error' => 'No division assigned'], 403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_checks' => QualityCheck::whereHas('batchOperation.operation', function($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })
                ->whereBetween('checked_at', [$startDate, $endDate])
                ->count(),
            'passed' => QualityCheck::whereHas('batchOperation.operation', function($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })
                ->where('result', 'pass')
                ->whereBetween('checked_at', [$startDate, $endDate])
                ->count(),
            'failed' => QualityCheck::whereHas('batchOperation.operation', function($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })
                ->where('result', 'fail')
                ->whereBetween('checked_at', [$startDate, $endDate])
                ->count(),
            'conditional' => QualityCheck::whereHas('batchOperation.operation', function($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })
                ->where('result', 'conditional_pass')
                ->whereBetween('checked_at', [$startDate, $endDate])
                ->count(),
        ];

        // Calculate pass rate
        $stats['pass_rate'] = $stats['total_checks'] > 0 
            ? round(($stats['passed'] / $stats['total_checks']) * 100, 2) 
            : 0;

        // Average pass rate per check
        $avgPassRate = QualityCheck::whereHas('batchOperation.operation', function($q) use ($user) {
                $q->where('division_id', $user->division_id);
            })
            ->whereBetween('checked_at', [$startDate, $endDate])
            ->get()
            ->avg(function($qc) {
                return $qc->getPassRate();
            });

        $stats['avg_pass_rate'] = round($avgPassRate ?? 0, 2);

        return response()->json($stats);
    }

    /**
     * Approve conditional pass (supervisor only)
     */
    public function approveConditional(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->isSupervisor() && !$user->isAdmin()) {
            return redirect()->back()
                ->with('error', 'Only supervisors can approve conditional pass.');
        }

        $qualityCheck = QualityCheck::with('batchOperation')->findOrFail($id);

        if ($qualityCheck->result != 'conditional_pass') {
            return redirect()->back()
                ->with('error', 'Only conditional pass results can be approved.');
        }

        $validated = $request->validate([
            'approval_notes' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Update quality check
            $qualityCheck->update([
                'notes' => ($qualityCheck->notes ?? '') . "\n\nSupervisor Approval: " . $validated['approval_notes']
            ]);

            // Update batch operation to passed
            $qualityCheck->batchOperation->update(['status' => 'qc_passed']);

            // Move to next operation if applicable
            $nextOp = $qualityCheck->batchOperation->batch->getNextOperation();
            if ($nextOp) {
                $nextOp->update(['status' => 'ready']);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Conditional pass approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Export QC report (CSV)
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        if (!$user->division_id) {
            return redirect()->back()
                ->with('error', 'No division assigned.');
        }

        $query = QualityCheck::with([
            'batchOperation.batch.poInternal',
            'batchOperation.operation',
            'checker'
        ])
        ->whereHas('batchOperation.operation', function($q) use ($user) {
            $q->where('division_id', $user->division_id);
        });

        // Apply filters
        if ($request->has('date_from')) {
            $query->whereDate('checked_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('checked_at', '<=', $request->date_to);
        }
        if ($request->has('result')) {
            $query->where('result', $request->result);
        }

        $qualityChecks = $query->orderBy('checked_at', 'desc')->get();

        $filename = 'qc-report-' . $user->division->code . '-' . date('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($qualityChecks) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Date',
                'Batch Number',
                'PO Number',
                'Operation',
                'Check Type',
                'Result',
                'Checked Qty',
                'Passed Qty',
                'Failed Qty',
                'Pass Rate (%)',
                'Checked By',
                'Defects',
            ]);

            // Data
            foreach ($qualityChecks as $qc) {
                fputcsv($file, [
                    $qc->checked_at->format('Y-m-d H:i'),
                    $qc->batchOperation->batch->batch_number,
                    $qc->batchOperation->batch->poInternal->po_number,
                    $qc->batchOperation->operation->name,
                    $qc->check_type,
                    $qc->result,
                    $qc->checked_quantity,
                    $qc->passed_quantity,
                    $qc->failed_quantity,
                    $qc->getPassRate(),
                    $qc->checker->name,
                    $qc->defect_description ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}