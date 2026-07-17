<?php

namespace App\Models;

use App\Enums\ProductUnit;
use App\Traits\BelongsToTenant;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant, HasAudit, HasFactory;

    protected $fillable = [
        'company_id', 'category_id', 'name', 'description', 'reference',
        'image_path', 'unit', 'sale_price', 'cost_price', 'low_stock_threshold',
        'is_active', 'is_favorite', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'unit' => ProductUnit::class,
            'sale_price' => 'integer',
            'cost_price' => 'integer',
            'is_active' => 'boolean',
            'is_favorite' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleLines(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }

    public function transferLines(): HasMany
    {
        return $this->hasMany(TransferLine::class);
    }
}
