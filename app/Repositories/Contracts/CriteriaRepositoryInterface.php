<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CriteriaRepositoryInterface extends RelationCriteriaInterface, DateCriteriaInterface
{
    /**
     * Apply where clause.
     */
    public function where(string $column, $operator = null, $value = null): self;

    /**
     * Apply or where clause.
     */
    public function orWhere(string $column, $operator = null, $value = null): self;

    /**
     * Apply nested where clause.
     */
    public function whereNested(callable $callback): self;

    /**
     * Apply or nested where clause.
     */
    public function orWhereNested(callable $callback): self;

    /**
     * Apply where in clause.
     */
    public function whereIn(string $column, array $values): self;

    /**
     * Apply or where in clause.
     */
    public function orWhereIn(string $column, array $values): self;

    /**
     * Apply where null clause.
     */
    public function whereNull(string $column): self;

    /**
     * Apply or where null clause.
     */
    public function orWhereNull(string $column): self;

    /**
     * Apply where not null clause.
     */
    public function whereNotNull(string $column): self;

    /**
     * Apply or where not null clause.
     */
    public function orWhereNotNull(string $column): self;

    /**
     * Apply order by clause.
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Get the query result.
     */
    public function get(array $columns = ['*']): Collection;

    /**
     * Get the underlying query builder with current criteria.
     */
    public function query(): Builder;

    /**
     * Get the underlying query builder and reset criteria state.
     */
    public function queryAndReset(): Builder;

    /**
     * Reset criteria state.
     */
    public function resetCriteria(): void;
}
