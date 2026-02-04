<?php

namespace App\Modules\Repair\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Repair\Exports\DamageReportsExport;
use App\Modules\Repair\Models\DamageReport;
use App\Modules\Repair\Models\Machine;
use App\Modules\Repair\Requests\StoreDamageReportRequest;
use App\Modules\Repair\Requests\UpdateDamageReportStatusRequest;
use App\Modules\Repair\Services\Contracts\DamageReportServiceInterface;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DamageReportController extends Controller
{
    public function __construct(
        protected DamageReportServiceInterface $damageReportService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'priority', 'department', 'location', 'machine', 'from', 'to']);

        $reports = $this->damageReportService->getFilteredReports($filters, $user);
        $departments = Machine::query()->select('department')->distinct()->pluck('department');

        return view('repair::reports.index', [
            'reports' => $reports,
            'filters' => $filters,
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        $machines = Machine::orderBy('code')->get();
        $technicians = User::role('repair.technician')->orderBy('name')->get();

        return view('repair::reports.create', compact('machines', 'technicians'));
    }

    public function store(StoreDamageReportRequest $request)
    {
        $validated = $request->validated();
        $beforePhotos = $request->file('before_photos', []);

        $report = $this->damageReportService->createReport(
            $validated,
            $request->user(),
            $beforePhotos
        );

        return redirect()
            ->route('repair.reports.show', $report)
            ->with('status', 'Damage report created successfully.');
    }

    public function show(DamageReport $damageReport, Request $request)
    {
        $damageReport->load([
            'machine',
            'reporter',
            'assignedTechnician',
            'receivedByForeman',
            'approvedByManager',
            'attachments',
            'histories.actor',
        ]);

        return view('repair::reports.show', [
            'report' => $damageReport,
            'beforeAttachments' => $damageReport->beforeAttachments()->get(),
            'afterAttachments' => $damageReport->afterAttachments()->get(),
            'allowedTransitions' => $this->damageReportService
                ->getAllowedTransitionsForUser($damageReport, $request->user()),
        ]);
    }

    public function updateStatus(UpdateDamageReportStatusRequest $request, DamageReport $damageReport)
    {
        $validated = $request->validated();
        $newStatus = $validated['status'];
        $user = $request->user();

        // Use role-aware validation
        if (!$this->damageReportService->canUserTransitionTo($damageReport, $newStatus, $user)) {
            return back()->withErrors([
                'status' => 'You do not have permission to perform this status transition.'
            ]);
        }

        $this->damageReportService->updateStatus(
            $damageReport,
            $newStatus,
            $user,
            $validated['notes'] ?? null,
            $request->file('after_photos', []),
            $validated['assigned_technician_id'] ?? null
        );

        return redirect()
            ->route('repair.reports.show', $damageReport)
            ->with('status', 'Status updated successfully.');
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'priority', 'department', 'machine', 'from', 'to']);

        // Managers should only export completed reports by default
        if ($user->hasRole('repair.manager') && !isset($filters['status'])) {
            $filters['status'] = DamageReport::STATUS_DONE_FIXING;
        }

        return Excel::download(new DamageReportsExport($filters, $user), 'damage-reports.xlsx');
    }
}
