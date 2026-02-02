<?php

namespace App\Modules\Repair\Repositories;

use App\Modules\Core\Repositories\BaseRepository;
use App\Modules\Repair\Models\Machine;
use App\Modules\Repair\Repositories\Contracts\MachineRepositoryInterface;

class MachineRepository extends BaseRepository implements MachineRepositoryInterface
{
    public function __construct(Machine $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Machine
    {
        return $this->model->where('code', $code)->first();
    }

    public function getByDepartment(string $department)
    {
        return $this->model->where('department', $department)->get();
    }
}
