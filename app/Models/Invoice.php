<?php

namespace App\Models;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use App\Exceptions\Business\InvoiceDeletionForbiddenException;
use App\Traits\BelongsToTenant;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use BelongsToTenant, HasAudit, HasFactory;

    protected $fillable = [
        'company_id', 'sale_id', 'number', 'issue_date', 'due_date',
        'total_amount', 'paid_amount', 'balance_due', 'payment_status',
        'delivery_status', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'integer',
            'paid_amount' => 'integer',
            'balance_due' => 'integer',
            'payment_status' => InvoicePaymentStatus::class,
            'delivery_status' => InvoiceDeliveryStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function receivable(): HasOne
    {
        return $this->hasOne(Receivable::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Une facture n'est jamais supprimable, quel que soit le point d'appel
     * (voir InvoiceDeletionForbiddenException) — seule l'annulation
     * (InvoiceService::cancel()) est permise.
     */
    protected static function booted(): void
    {
        static::deleting(function (Invoice $invoice) {
            throw new InvoiceDeletionForbiddenException;
        });
    }
}
