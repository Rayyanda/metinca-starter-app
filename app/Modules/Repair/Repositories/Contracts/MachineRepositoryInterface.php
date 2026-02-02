<?php

namespace App\Modules\Repair\Repositories\Contracts;

use App\Modules\Core\Contracts\RepositoryInterface;
use App\Modules\Repair\Models\Machine;

interface MachineRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $code): ?Machine;

    public function getByDepartment(string $department);
}
