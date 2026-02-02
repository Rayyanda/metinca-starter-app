<?php

namespace App\Modules\Repair\Repositories;

use App\Models\User;
use App\Modules\Core\Repositories\BaseRepository;
use App\Modules\Repair\Models\DamageReport;
use App\Modules\Repair\Repositories\Contracts\DamageReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class DamageReportRepository extends BaseRepository implements DamageReportRepositoryInterface
{
    public function __construct(DamageReport $model)
    {
        parent::__construct($model);
    }

    public function getFiltered(array $filters, User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['machine', 'reporter', 'assignedTechnician'])
            ->filter($filters)
            ->orderByDesc('reported_at');

        if ($user->hasRole('super_admin') || $user->hasAnyRole(['repair.supervisor', 'repair.manager'])) {
            // Can see all reports
        } elseif ($user->hasRole('repair.user')) {
            $query->where('reported_by', $user->id);
        } elseif ($user->hasRole('repair.technician')) {
            $query->where('assigned_technician_id', $user->id);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findByCode(string $code): ?DamageReport
    {
        return $this->model->where('report_code', $code)->first();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function getPendingDeadlineReports(): Collection
    {
        $today = Carbon::today();
        $twoDaysFromNow = $today->copy()->addDays(2);

        return $this->model
            ->with(['assignedTechnician'])
            ->where('status', '!=', DamageReport::STATUS_DONE)
            ->whereNotNull('target_completed_at')
            ->whereBetween('target_completed_at', [$today, $twoDaysFromNow])
            ->get();
    }

    public function getOverdueReports(): Collection
    {
        return $this->model
            ->with(['assignedTechnician'])
            ->where('status', '!=', DamageReport::STATUS_DONE)
            ->whereNotNull('target_completed_at')
            ->where('target_completed_at', '<', Carbon::today())
            ->get();
    }
}
