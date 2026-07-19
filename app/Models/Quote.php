<?php

namespace App\Models;

use App\Enums\CustomerType;
use App\Enums\QuoteStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'company_id',
        'number',
        'outlet_id',
        'user_id',
        'customer_id',
        'customer_type',
        'valid_until',
        'total_amount',
        'discount_amount',
        'discount_percentage',
        'status',
        'converted_sale_id',
        'converted_at',
        'converted_by',
        'notes',
    ];

    protected $casts = [
        'customer_type'        => CustomerType::class,
        'status'               => QuoteStatus::class,
        'total_amount'         => 'integer',
        'discount_amount'      => 'integer',
        'discount_percentage'  => 'integer',
        'valid_until'          => 'date',
        'converted_at'         => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quoteLines(): HasMany
    {
        return $this->hasMany(QuoteLine::class);
    }

    public function convertedSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'converted_sale_id');
    }

    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function netTotal(): int
    {
        return max(0, $this->total_amount - $this->discount_amount);
    }
}
