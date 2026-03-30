<?php

namespace App\Services;

use App\Repositories\Contracts\CrudRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseService
{
    public function __construct(
        protected CrudRepositoryInterface $repository
    ) {
    }

    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    /**
     * Find one record by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->repository->findOrFail($id, $columns);
    }

    /**
     * Create one record.
     */
    public function createRecord(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update one record.
     */
    public function updateRecord(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete one record.
     */
    public function deleteRecord(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns);
    }
}
