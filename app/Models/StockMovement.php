<?php

namespace App\Models;

use App\Enums\LocationType;
use App\Enums\StockMovementType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToTenant, HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'company_id', 'product_id', 'movement_type', 'quantity',
        'location_source_type', 'location_source_id',
        'location_destination_type', 'location_destination_id',
        'reason', 'user_id', 'movement_date', 'document_type', 'document_id', 'note',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => StockMovementType::class,
            'location_source_type' => LocationType::class,
            'location_destination_type' => LocationType::class,
            'quantity' => 'integer',
            'movement_date' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
