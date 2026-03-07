<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Store;

/**
 * StoreScope Trait
 *
 * Apply this trait to any Eloquent model that has a `store_id` column.
 * It automatically scopes queries to the currently active store for the
 * logged-in user, enabling full multi-store data isolation.
 *
 * USAGE:
 *   Add `use StoreScope;` to Sale, Purchase, Expense, etc. models.
 *
 * The scope is automatically applied via a global scope in boot().
 * Super admin (company_id = null) sees ALL stores/data.
 *
 * You can also manually use it:
 *   Sale::forStore(5)->get();
 *   Sale::withoutStoreScope()->get(); // bypass for reporting
 */
trait StoreScope
{
    /**
     * Boot the trait — registers the global store scope.
     */
    public static function bootStoreScope(): void
    {
        static::addGlobalScope('store', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }

            $user    = auth()->user();
            $storeId = $user->getActiveStoreId();

            // Super admin (no company_id) — no store restriction
            if (is_null($storeId)) {
                return;
            }

            $builder->where(static::getStoreColumn(), $storeId);
        });
    }

    /**
     * The column name used for store scoping (default: store_id).
     * Override in your model if the column name differs.
     */
    protected static function getStoreColumn(): string
    {
        return 'store_id';
    }

    // ---------------------------------------------------------------
    // Scope helpers you can use in queries
    // ---------------------------------------------------------------

    /**
     * Filter results for a specific store.
     *
     * @param Builder $query
     * @param int $storeId
     * @return Builder
     */
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->withoutGlobalScope('store')
                     ->where(static::getStoreColumn(), $storeId);
    }

    /**
     * Remove the store scope — useful for cross-store reports.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAllStores(Builder $query): Builder
    {
        return $query->withoutGlobalScope('store');
    }

    /**
     * Filter results for all stores belonging to a specific client.
     *
     * @param Builder $query
     * @param int $clientId
     * @return Builder
     */
    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        $storeIds = Store::where('client_id', $clientId)->pluck('id');
        return $query->withoutGlobalScope('store')
                     ->whereIn(static::getStoreColumn(), $storeIds);
    }

    // ---------------------------------------------------------------
    // Relationship helper
    // ---------------------------------------------------------------

    /**
     * Relationship to the store this record belongs to.
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * Automatically set store_id when creating a record.
     * Called from the model's boot() method.
     */
    protected static function bootStoreScopeAutoAssign(): void
    {
        static::creating(function ($model) {
            if (empty($model->store_id) && auth()->check()) {
                $model->store_id = auth()->user()->getActiveStoreId();
            }
        });
    }
}
