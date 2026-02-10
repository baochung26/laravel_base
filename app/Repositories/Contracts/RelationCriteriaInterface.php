<?php

namespace App\Repositories\Contracts;

interface RelationCriteriaInterface
{
    /**
     * Get models with relationships.
     */
    public function with(array $relations): self;

    /**
     * Apply where has clause.
     */
    public function whereHas(string $relation, ?callable $callback = null): self;

    /**
     * Apply or where has clause.
     */
    public function orWhereHas(string $relation, ?callable $callback = null): self;
}
