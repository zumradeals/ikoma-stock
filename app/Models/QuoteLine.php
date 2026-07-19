<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    protected $fillable = [
        'quote_id',
        'product_id',
        'quantity',
        'unit_price',
        'line_discount',
        'line_total',
    ];

    protected $casts = [
        'quantity'      => 'integer',
        'unit_price'    => 'integer',
        'line_discount' => 'integer',
        'line_total'    => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
