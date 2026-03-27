<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasStoreScope
{
    /**
     * Boot the trait to apply the global scope and creating event.
     */
    protected static function bootHasStoreScope()
    {
        // Global scope to add store_id to all select queries
        static::addGlobalScope('store', function (Builder $builder) {
            // Check if user is logged in
            if (auth()->check()) {
                $storeId = auth()->user()->getActiveStoreId();
                if (!is_null($storeId)) {
                    $builder->where((new static)->getTable() . '.store_id', $storeId);
                }
            }
        });

        // Automatically add store_id when creating models
        static::creating(function (Model $model) {
            if (auth()->check() && empty($model->store_id)) {
                $storeId = auth()->user()->getActiveStoreId();
                if (!is_null($storeId)) {
                    $model->store_id = $storeId;
                }
            }
        });
    }

    /**
     * Scope to allow fetching records across all stores (or without store filter).
     */
    public function scopeWithoutStore(Builder $query)
    {
        return $query->withoutGlobalScope('store');
    }
}
