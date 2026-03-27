<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientStoreUsers extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'company_id',
        'store_id',
        'user_id',
        'role_id'
    ];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * The client (customer) this store belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class , 'client_id');
    }

    /**
     * The company profile/settings for this store.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class , 'company_id');
    }

    /**
     * Users assigned to this store via the pivot table.
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    /**
     * Users assigned to this store via the pivot table.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class , 'role_id');
    }
}
