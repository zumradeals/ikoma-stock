<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleLine extends Model
{
    use HasAudit, HasFactory;

    protected $fillable = [
        'sale_id', 'product_id', 'quantity', 'unit_price', 'line_discount', 'line_total',
        'delivered_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'line_discount' => 'integer',
            'line_total' => 'integer',
            'delivered_quantity' => 'integer',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryLines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeliveryLine::class);
    }

    public function remainingToDeliver(): int
    {
        return $this->quantity - $this->delivered_quantity;
    }
}
