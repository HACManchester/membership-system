<?php

namespace BB\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope as ScopeInterface;

/**
 * Scope class to add to models to set their default ordering
 * 
 * @see https://stackoverflow.com/a/30662338
 */
class OrderScope implements ScopeInterface
{
    protected $column;

    protected $direction;

    public function __construct($column, $direction = 'asc')
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    public function apply(Builder $builder, Model $model)
    {
        $builder->orderBy($this->column, $this->direction);

        // optional macro to undo the global scope
        $builder->macro('unordered', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());
            return $builder;
        });
    }

    public function remove(Builder $builder, Model $model)
    {
        $query = $builder->getQuery();
        $query->orders = collect($query->orders)->reject(function ($order) {
            return $order['column'] == $this->column && $order['direction'] == $this->direction;
        })->values()->all();
        if (count($query->orders) == 0) {
            $query->orders = null;
        }
    }
}
