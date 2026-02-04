<?php

namespace App\Modules\Repair\Exports;

use App\Models\User;
use App\Modules\Repair\Models\DamageReport;
use Illuminate\Contracts\Auth\Authenticatable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DamageReportsExport implements FromCollection, WithMapping, WithHeadings
{
    public function __construct(
        private readonly array $filters,
        private readonly User|Authenticatable $user
    ) {}

    public function collection()
    {
        $query = DamageReport::with(['machine', 'reporter', 'assignedTechnician'])->filter($this->filters);

        if ($this->user->hasRole('repair.user')) {
            $query->where('reported_by', $this->user->id);
        } elseif ($this->user->hasRole('repair.technician')) {
            $query->where('assigned_technician_id', $this->user->id)
                 ->whereIn('status', [
                     DamageReport::STATUS_APPROVED_BY_MANAGER,
                     DamageReport::STATUS_ON_FIXING_PROGRESS,
                     DamageReport::STATUS_DONE_FIXING,
                 ]);
        } elseif ($this->user->hasRole('repair.manager')) {
            // Managers export only completed reports unless filtered otherwise
            if (!isset($this->filters['status'])) {
                $query->where('status', DamageReport::STATUS_DONE_FIXING);
            }
        }

        return $query->orderByDesc('reported_at')->get();
    }

    public function map($report): array
    {
        return [
            $report->report_code,
            $report->machine->code ?? '',
            $report->department,
            $report->damage_type_other ?: $report->damage_type,
            ucfirst($report->priority),
            $report->status_label,
            optional($report->reported_at)->format('Y-m-d H:i'),
            optional($report->target_completed_at)->format('Y-m-d'),
            optional($report->actual_completed_at)->format('Y-m-d H:i'),
            $report->reporter->name ?? '',
            $report->assignedTechnician->name ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'Report ID',
            'Machine Number',
            'Department/Location',
            'Damage Type',
            'Priority',
            'Status',
            'Reported At',
            'Target Completion',
            'Actual Completion',
            'Reporter',
            'Technician',
        ];
    }
}
