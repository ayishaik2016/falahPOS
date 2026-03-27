<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\UserWarehouse;
use App\Models\OrderedProduct;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'username',
        'role_id',
        'company_id',
        'status',
        'avatar',
        'mobile',
        'company_id',
        'store_id',             
        'is_allowed_all_warehouses',
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Join with roles table
     * */
    public function orderedProducts(): BelongsTo
    {
        return $this->belongsTo(OrderedProduct::class, 'assigned_user_id');
    }

    /**
     * Join with user_warehouses table
     * */
    public function userWarehouses(): HasMany
    {
        return $this->hasMany(UserWarehouse::class, 'user_id');
    }

    /**
     * The default store this user is primarily assigned to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    /**
     * All stores this user has access to (via client_store_users pivot).
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'client_store_users', 'user_id', 'store_id')
                    ->withPivot('client_id', 'role')
                    ->withTimestamps();
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Returns the currently active store_id.
     * Super admin (company_id = null) has no store restriction.
     * Store users use session-selected store or fall back to their default.
     */
    public function getActiveStoreId(): ?int
    {
        // Use session-selected store if set
        if (session()->has('active_store_id')) {
            return (int) session('active_store_id');
        }

        // Super admin — no store scoping
        if (is_null($this->company_id)) {
            return null;
        }

        return $this->store_id;
    }

    /**
     * Returns warehouses accessible to this user, scoped by active store.
     */
    public function getAccessibleWarehouses(bool $viewAllWarehouse = false)
    {
        $storeId = $this->getActiveStoreId();

        $query = Warehouse::query();

        // Scope to the user's active store if they have one
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($this->is_allowed_all_warehouses || $viewAllWarehouse) {
            return $query->get();
        }

        $warehouseIds = UserWarehouse::where('user_id', $this->id)->pluck('warehouse_id');
        return $query->whereIn('id', $warehouseIds)->get();
    }
}
