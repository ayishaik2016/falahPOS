<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'company_id',
        'name',
        'code',
        'address',
        'mobile',
        'email',
        'status',
        'is_default',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * The client (customer) this store belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * The company profile/settings for this store.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Warehouses that belong to this store.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class, 'store_id');
    }

    /**
     * Users assigned to this store via the pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_store_users', 'store_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Scope: only active stores.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
