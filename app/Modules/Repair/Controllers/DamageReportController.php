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

    public function show(DamageReport $damageReport)
    {
        $damageReport->load([
            'machine',
            'reporter',
            'assignedTechnician',
            'attachments',
            'histories.actor',
        ]);

        return view('repair::reports.show', [
            'report' => $damageReport,
            'beforeAttachments' => $damageReport->beforeAttachments()->get(),
            'afterAttachments' => $damageReport->afterAttachments()->get(),
        ]);
    }

    public function updateStatus(UpdateDamageReportStatusRequest $request, DamageReport $damageReport)
    {
        $validated = $request->validated();
        $newStatus = $validated['status'];

        if (!$this->damageReportService->canTransitionTo($damageReport, $newStatus)) {
            return back()->withErrors(['status' => 'Invalid status transition.']);
        }

        $this->damageReportService->updateStatus(
            $damageReport,
            $newStatus,
            $request->user(),
            $validated['notes'] ?? null,
            $request->file('after_photos', [])
        );

        return redirect()
            ->route('repair.reports.show', $damageReport)
            ->with('status', 'Status updated successfully.');
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'priority', 'department', 'machine', 'from', 'to']);

        return Excel::download(new DamageReportsExport($filters, $user), 'damage-reports.xlsx');
    }
}
