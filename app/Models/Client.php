<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Client model — represents a customer/tenant who owns one or more stores.
 *
 * CHANGES (multi-store):
 *  - Added `stores()` HasMany relationship
 *  - Added `defaultStore()` helper
 */
class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'whatsapp',
        'avatar',
        'status',
        'company_id',
        'user_id',
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
     * All stores belonging to this client.
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'client_id');
    }

    /**
     * The default/primary store for this client.
     */
    public function defaultStore(): ?Store
    {
        return $this->stores()->where('is_default', 1)->first();
    }

    /**
     * The user account associated with this client (for login).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The main company record for this client.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
