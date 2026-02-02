<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected array $with = [];
    protected array $orderBy = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->buildQuery()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->buildQuery()->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->buildQuery()->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->buildQuery()->findOrFail($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->buildQuery()->where($field, $value)->first($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(int $id, array $attributes): bool
    {
        return $this->findOrFail($id)->update($attributes);
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orderBy[] = ['column' => $column, 'direction' => $direction];
        return $this;
    }

    protected function buildQuery()
    {
        $query = $this->model->newQuery();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        foreach ($this->orderBy as $order) {
            $query->orderBy($order['column'], $order['direction']);
        }

        $this->with = [];
        $this->orderBy = [];

        return $query;
    }

    protected function resetQuery(): void
    {
        $this->with = [];
        $this->orderBy = [];
    }
}
