<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Party\Party;
use App\Models\Items\ItemTransaction;
use App\Traits\FormatsDateInputs;
use App\Traits\FormatTime;
use App\Traits\StoreScope;          // NEW
use App\Models\PaymentTransaction;
use App\Models\Accounts\AccountTransaction;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CHANGES (multi-store):
 *  - Added `use StoreScope` trait — automatically filters by active store
 *  - Added `store_id` to $fillable
 */
class Sale extends Model
{
    use HasFactory, FormatsDateInputs, FormatTime, StoreScope;

    protected $fillable = [
        'sale_date',
        'sale_order_id',
        'quotation_id',
        'prefix_code',
        'count_id',
        'sale_code',
        'reference_no',
        'party_id',
        'state_id',
        'note',
        'round_off',
        'grand_total',
        'paid_amount',
        'currency_id',
        'exchange_rate',
        'store_id',         // NEW
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();

            // Auto-assign store_id from active user session
            if (empty($model->store_id) && auth()->check()) {
                $model->store_id = auth()->user()->getActiveStoreId();
            }
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    public function getFormattedSaleDateAttribute()
    {
        return $this->toUserDateFormat($this->sale_date);
    }

    public function getFormatCreatedTimeAttribute()
    {
        return $this->toUserTimeFormat($this->created_at);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function itemTransaction(): MorphMany
    {
        return $this->morphMany(ItemTransaction::class, 'transaction');
    }

    public function paymentTransaction(): MorphMany
    {
        return $this->morphMany(PaymentTransaction::class, 'transaction');
    }

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function saleReturn(): HasMany
    {
        return $this->hasMany(SaleReturn::class, 'reference_no', 'sale_code');
    }

    public function accountTransaction(): MorphMany
    {
        return $this->morphMany(AccountTransaction::class, 'transaction');
    }

    public function getTableCode()
    {
        return $this->sale_code;
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
