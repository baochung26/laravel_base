<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance.
     */
    protected Model $model;

    /**
     * Relations to eager load.
     */
    protected array $with = [];

    /**
     * Where conditions.
     */
    protected array $wheres = [];

    /**
     * Order by conditions.
     */
    protected array $orderBy = [];

    /**
     * Create a new repository instance.
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     */
    abstract protected function model(): string;

    /**
     * Make Model instance.
     */
    protected function makeModel(): Model
    {
        $model = app($this->model());

        if (! $model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Reset the query builder.
     */
    protected function resetModel(): void
    {
        $this->makeModel();
        $this->with = [];
        $this->wheres = [];
        $this->orderBy = [];
    }

    /**
     * Get all models.
     */
    public function all(array $columns = ['*']): Collection
    {
        $query = $this->applyCriteria();
        $result = $query->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Find model by ID.
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        $query = $this->applyCriteria();
        $result = $query->find($id, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Find model by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $query = $this->applyCriteria();
        $result = $query->findOrFail($id, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Find model by field.
     */
    public function findBy(string $field, $value, array $columns = ['*']): ?Model
    {
        $query = $this->applyCriteria();
        $result = $query->where($field, $value)->first($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Find models by field.
     */
    public function findAllBy(string $field, $value, array $columns = ['*']): Collection
    {
        $query = $this->applyCriteria();
        $result = $query->where($field, $value)->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Create a new model.
     */
    public function create(array $data): Model
    {
        $result = $this->model->create($data);

        $this->resetModel();

        return $result;
    }

    /**
     * Update a model.
     */
    public function update(int $id, array $data): bool
    {
        $query = $this->applyCriteria();
        $result = $query->findOrFail($id)->update($data);

        $this->resetModel();

        return $result;
    }

    /**
     * Update or create a model.
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        $result = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        return $result;
    }

    /**
     * Delete a model.
     */
    public function delete(int $id): bool
    {
        $query = $this->applyCriteria();
        $result = $query->findOrFail($id)->delete();

        $this->resetModel();

        return $result;
    }

    /**
     * Get paginated models.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->applyCriteria();
        $result = $query->paginate($perPage, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Get models with relationships.
     */
    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);

        return $this;
    }

    /**
     * Apply where clause.
     */
    public function where(string $column, $operator = null, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact('column', 'operator', 'value');

        return $this;
    }

    /**
     * Apply order by clause.
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orderBy[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Get the query result.
     */
    public function get(array $columns = ['*']): Collection
    {
        $query = $this->applyCriteria();
        $result = $query->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * Apply all criteria to the model.
     */
    protected function applyCriteria(): Builder
    {
        $query = $this->model->newQuery();

        // Apply eager loading
        if (! empty($this->with)) {
            $query = $query->with($this->with);
        }

        // Apply where conditions
        foreach ($this->wheres as $where) {
            $query = $query->where($where['column'], $where['operator'], $where['value']);
        }

        // Apply order by
        foreach ($this->orderBy as $order) {
            $query = $query->orderBy($order['column'], $order['direction']);
        }

        return $query;
    }
}
