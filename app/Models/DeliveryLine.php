<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLine extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'delivery_id', 'sale_line_id', 'product_id', 'quantity_delivered',
    ];

    protected function casts(): array
    {
        return [
            'quantity_delivered' => 'integer',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function saleLine(): BelongsTo
    {
        return $this->belongsTo(SaleLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
