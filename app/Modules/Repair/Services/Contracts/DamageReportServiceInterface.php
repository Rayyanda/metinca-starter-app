<?php

namespace App\Modules\Repair\Services\Contracts;

use App\Models\User;
use App\Modules\Repair\Models\DamageReport;
use Illuminate\Pagination\LengthAwarePaginator;

interface DamageReportServiceInterface
{
    public function getFilteredReports(array $filters, User $user, int $perPage = 15): LengthAwarePaginator;

    public function createReport(array $data, User $reporter, array $beforePhotos = []): DamageReport;

    public function updateStatus(
        DamageReport $report,
        string $newStatus,
        User $actor,
        ?string $notes = null,
        array $afterPhotos = []
    ): DamageReport;

    public function addAttachments(DamageReport $report, array $files, string $type, User $uploader): void;

    public function canTransitionTo(DamageReport $report, string $newStatus): bool;

    public function sendDeadlineReminders(): int;
}
