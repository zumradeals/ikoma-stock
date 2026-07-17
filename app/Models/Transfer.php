<?php

namespace App\Models;

use App\Enums\TransferStatus;
use App\Traits\BelongsToTenant;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use BelongsToTenant, HasAudit, HasFactory;

    protected $fillable = [
        'company_id', 'number', 'source_warehouse_id', 'source_outlet_id',
        'destination_warehouse_id', 'destination_outlet_id', 'user_id', 'status',
        'total_quantity', 'shipped_quantity', 'received_quantity',
        'request_date', 'ship_date', 'receive_date', 'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatus::class,
            'total_quantity' => 'integer',
            'shipped_quantity' => 'integer',
            'received_quantity' => 'integer',
            'request_date' => 'datetime',
            'ship_date' => 'datetime',
            'receive_date' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function sourceOutlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'source_outlet_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function destinationOutlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'destination_outlet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transferLines(): HasMany
    {
        return $this->hasMany(TransferLine::class);
    }
}
