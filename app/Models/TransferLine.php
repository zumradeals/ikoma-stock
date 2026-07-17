<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferLine extends Model
{
    use HasAudit, HasFactory;

    protected $fillable = [
        'transfer_id', 'product_id', 'requested_quantity', 'shipped_quantity', 'received_quantity',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'integer',
            'shipped_quantity' => 'integer',
            'received_quantity' => 'integer',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
