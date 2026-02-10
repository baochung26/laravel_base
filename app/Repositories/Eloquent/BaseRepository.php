<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\CrudRepositoryInterface;
use App\Repositories\Contracts\CriteriaRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements CrudRepositoryInterface, CriteriaRepositoryInterface
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
     * Criteria conditions (keeps call order).
     */
    protected array $criteria = [];

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
        $this->criteria = [];
        $this->orderBy = [];
    }

    /**
     * Reset criteria state (relations, conditions, order).
     */
    public function resetCriteria(): void
    {
        $this->with = [];
        $this->criteria = [];
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

        $this->criteria[] = [
            'type' => 'where',
            'boolean' => 'and',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Apply or where clause.
     */
    public function orWhere(string $column, $operator = null, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->criteria[] = [
            'type' => 'where',
            'boolean' => 'or',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Apply nested where clause.
     */
    public function whereNested(callable $callback): self
    {
        $this->criteria[] = [
            'type' => 'nested',
            'boolean' => 'and',
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * Apply or nested where clause.
     */
    public function orWhereNested(callable $callback): self
    {
        $this->criteria[] = [
            'type' => 'nested',
            'boolean' => 'or',
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * Apply where in clause.
     */
    public function whereIn(string $column, array $values): self
    {
        $this->criteria[] = [
            'type' => 'whereIn',
            'boolean' => 'and',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * Apply or where in clause.
     */
    public function orWhereIn(string $column, array $values): self
    {
        $this->criteria[] = [
            'type' => 'whereIn',
            'boolean' => 'or',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * Apply where null clause.
     */
    public function whereNull(string $column): self
    {
        $this->criteria[] = [
            'type' => 'whereNull',
            'boolean' => 'and',
            'column' => $column,
        ];

        return $this;
    }

    /**
     * Apply or where null clause.
     */
    public function orWhereNull(string $column): self
    {
        $this->criteria[] = [
            'type' => 'whereNull',
            'boolean' => 'or',
            'column' => $column,
        ];

        return $this;
    }

    /**
     * Apply where not null clause.
     */
    public function whereNotNull(string $column): self
    {
        $this->criteria[] = [
            'type' => 'whereNotNull',
            'boolean' => 'and',
            'column' => $column,
        ];

        return $this;
    }

    /**
     * Apply or where not null clause.
     */
    public function orWhereNotNull(string $column): self
    {
        $this->criteria[] = [
            'type' => 'whereNotNull',
            'boolean' => 'or',
            'column' => $column,
        ];

        return $this;
    }

    /**
     * Apply where between clause.
     */
    public function whereBetween(string $column, array $values): self
    {
        $this->criteria[] = [
            'type' => 'whereBetween',
            'boolean' => 'and',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * Apply or where between clause.
     */
    public function orWhereBetween(string $column, array $values): self
    {
        $this->criteria[] = [
            'type' => 'whereBetween',
            'boolean' => 'or',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
    }

    /**
     * Apply where date clause.
     */
    public function whereDate(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->criteria[] = [
            'type' => 'whereDate',
            'boolean' => 'and',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Apply or where date clause.
     */
    public function orWhereDate(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->criteria[] = [
            'type' => 'whereDate',
            'boolean' => 'or',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }
    /**
     * Apply where has clause.
     */
    public function whereHas(string $relation, ?callable $callback = null): self
    {
        $this->criteria[] = [
            'type' => 'whereHas',
            'boolean' => 'and',
            'relation' => $relation,
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * Apply or where has clause.
     */
    public function orWhereHas(string $relation, ?callable $callback = null): self
    {
        $this->criteria[] = [
            'type' => 'whereHas',
            'boolean' => 'or',
            'relation' => $relation,
            'callback' => $callback,
        ];

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
     * Get the underlying query builder with current criteria.
     */
    public function query(): Builder
    {
        $query = $this->applyCriteria();

        return $query;
    }

    /**
     * Get the underlying query builder and reset criteria state.
     */
    public function queryAndReset(): Builder
    {
        $query = $this->applyCriteria();
        $this->resetCriteria();

        return $query;
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

        // Apply criteria in the same order they were added
        foreach ($this->criteria as $criterion) {
            $boolean = $criterion['boolean'] ?? 'and';
            switch ($criterion['type']) {
                case 'where':
                    $query = $query->where(
                        $criterion['column'],
                        $criterion['operator'],
                        $criterion['value'],
                        $boolean
                    );
                    break;
                case 'nested':
                    $query = $boolean === 'or'
                        ? $query->orWhere($criterion['callback'])
                        : $query->where($criterion['callback']);
                    break;
                case 'whereIn':
                    $query = $boolean === 'or'
                        ? $query->orWhereIn($criterion['column'], $criterion['values'])
                        : $query->whereIn($criterion['column'], $criterion['values']);
                    break;
                case 'whereBetween':
                    $query = $boolean === 'or'
                        ? $query->orWhereBetween($criterion['column'], $criterion['values'])
                        : $query->whereBetween($criterion['column'], $criterion['values']);
                    break;
                case 'whereNull':
                    $query = $boolean === 'or'
                        ? $query->orWhereNull($criterion['column'])
                        : $query->whereNull($criterion['column']);
                    break;
                case 'whereNotNull':
                    $query = $boolean === 'or'
                        ? $query->orWhereNotNull($criterion['column'])
                        : $query->whereNotNull($criterion['column']);
                    break;
                case 'whereDate':
                    $query = $query->whereDate(
                        $criterion['column'],
                        $criterion['operator'],
                        $criterion['value'],
                        $boolean
                    );
                    break;
                case 'whereHas':
                    if ($criterion['callback']) {
                        $query = $boolean === 'or'
                            ? $query->orWhereHas($criterion['relation'], $criterion['callback'])
                            : $query->whereHas($criterion['relation'], $criterion['callback']);
                        break;
                    }

                    $query = $boolean === 'or'
                        ? $query->orWhereHas($criterion['relation'])
                        : $query->whereHas($criterion['relation']);
                    break;
            }
        }

        // Apply order by
        foreach ($this->orderBy as $order) {
            $query = $query->orderBy($order['column'], $order['direction']);
        }

        return $query;
    }
}
