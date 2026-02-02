<?php

namespace App\Modules\Repair\Repositories\Contracts;

use App\Models\User;
use App\Modules\Core\Contracts\RepositoryInterface;
use App\Modules\Repair\Models\DamageReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DamageReportRepositoryInterface extends RepositoryInterface
{
    public function getFiltered(array $filters, User $user, int $perPage = 15): LengthAwarePaginator;

    public function findByCode(string $code): ?DamageReport;

    public function getByStatus(string $status): Collection;

    public function getPendingDeadlineReports(): Collection;

    public function getOverdueReports(): Collection;
}
