<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $query) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            $column = $query->getModel()->getTable() . '.id_business';

            $user->id_business
                ? $query->where($column, $user->id_business)
                : $query->whereRaw('1 = 0');
        });

        static::creating(function ($model) {
            if (! $model->id_business && auth()->check()) {
                $model->id_business = auth()->user()->id_business;
            }
        });
    }
}
