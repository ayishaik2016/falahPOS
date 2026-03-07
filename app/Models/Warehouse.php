<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * CHANGES (multi-store):
 *  - Added `store_id` to $fillable
 *  - Added `store()` BelongsTo relationship
 */
class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'store_id',     // NEW
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();

            // Auto-assign store_id from logged-in user's active store if not provided
            if (empty($model->store_id) && auth()->check()) {
                $model->store_id = auth()->user()->getActiveStoreId();
            }
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });

        static::created(function ($warehouse) {
            Cache::forget('warehouse');
        });
        static::updated(function ($warehouse) {
            Cache::forget('warehouse');
        });
        static::deleted(function ($warehouse) {
            Cache::forget('warehouse');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The store this warehouse belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
