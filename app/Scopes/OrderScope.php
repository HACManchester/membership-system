<?php

namespace BB\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope as ScopeInterface;

/**
 * Scope class to add to models to set their default ordering, by name unless
 * constructed otherwise; #[ScopedBy(OrderScope::class)] gets the default. Undo
 * it for a query with ->withoutGlobalScope(OrderScope::class).
 */
class OrderScope implements ScopeInterface
{
    protected $column;

    protected $direction;

    public function __construct($column = 'name', $direction = 'asc')
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->orderBy($this->column, $this->direction);
    }
}
