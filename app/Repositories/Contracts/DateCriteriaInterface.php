<?php

namespace App\Repositories\Contracts;

interface DateCriteriaInterface
{
    /**
     * Apply where between clause.
     */
    public function whereBetween(string $column, array $values): self;

    /**
     * Apply or where between clause.
     */
    public function orWhereBetween(string $column, array $values): self;

    /**
     * Apply where date clause.
     */
    public function whereDate(string $column, $operator, $value = null): self;

    /**
     * Apply or where date clause.
     */
    public function orWhereDate(string $column, $operator, $value = null): self;
}
