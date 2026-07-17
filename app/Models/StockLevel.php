<?php

namespace App\Models;

use App\Enums\LocationType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'company_id', 'product_id', 'location_type', 'location_id',
        'quantity_physical', 'quantity_reserved',
    ];

    protected function casts(): array
    {
        return [
            'location_type' => LocationType::class,
            'quantity_physical' => 'integer',
            'quantity_reserved' => 'integer',
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

    /**
     * Relation polymorphe manuelle : location_type ne stocke pas de FQCN
     * (juste WAREHOUSE/OUTLET), donc pas de morphTo() standard. Résolution
     * à l'instance uniquement — ne pas utiliser avec with()/eager loading.
     */
    public function location(): BelongsTo
    {
        return $this->location_type === LocationType::OUTLET
            ? $this->belongsTo(Outlet::class, 'location_id')
            : $this->belongsTo(Warehouse::class, 'location_id');
    }
}
