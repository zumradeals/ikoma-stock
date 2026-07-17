<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant, HasAudit, HasFactory;

    protected $fillable = [
        'company_id', 'name', 'phone', 'address', 'neighborhood_city', 'tax_id',
        'credit_limit', 'notes', 'is_active', 'total_purchased', 'outstanding_balance',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'integer',
            'is_active' => 'boolean',
            'total_purchased' => 'integer',
            'outstanding_balance' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(Receivable::class);
    }
}
