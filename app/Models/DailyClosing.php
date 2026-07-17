<?php

namespace App\Models;

use App\Enums\DailyClosingStatus;
use App\Traits\BelongsToTenant;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyClosing extends Model
{
    use BelongsToTenant, HasAudit, HasFactory;

    protected $fillable = [
        'company_id', 'outlet_id', 'user_id', 'business_date', 'total_sales',
        'cash_sales', 'mobile_money_sales', 'transfer_sales', 'credit_sales',
        'collected_old_receivables', 'total_discounts', 'cancelled_invoices_count',
        'delivered_products_count', 'declared_cash_amount', 'cash_difference',
        'observations', 'status', 'validated_by_user_id', 'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'total_sales' => 'integer',
            'cash_sales' => 'integer',
            'mobile_money_sales' => 'integer',
            'transfer_sales' => 'integer',
            'credit_sales' => 'integer',
            'collected_old_receivables' => 'integer',
            'total_discounts' => 'integer',
            'declared_cash_amount' => 'integer',
            'cash_difference' => 'integer',
            'status' => DailyClosingStatus::class,
            'validated_at' => 'datetime',
        ];
    }

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

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
