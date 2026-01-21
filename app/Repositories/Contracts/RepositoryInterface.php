<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Get all models.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find model by ID.
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find model by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Find model by field.
     */
    public function findBy(string $field, $value, array $columns = ['*']): ?Model;

    /**
     * Find models by field.
     */
    public function findAllBy(string $field, $value, array $columns = ['*']): Collection;

    /**
     * Create a new model.
     */
    public function create(array $data): Model;

    /**
     * Update a model.
     */
    public function update(int $id, array $data): bool;

    /**
     * Update or create a model.
     */
    public function updateOrCreate(array $attributes, array $values = []): Model;

    /**
     * Delete a model.
     */
    public function delete(int $id): bool;

    /**
     * Get paginated models.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Get models with relationships.
     */
    public function with(array $relations): self;

    /**
     * Apply where clause.
     */
    public function where(string $column, $operator = null, $value = null): self;

    /**
     * Apply order by clause.
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Get the query result.
     */
    public function get(array $columns = ['*']): Collection;
}
