<?php

namespace App\Models;

use App\Enums\ReceivableStatus;
use App\Traits\BelongsToTenant;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receivable extends Model
{
    use BelongsToTenant, HasAudit, HasFactory;

    protected $fillable = [
        'company_id', 'invoice_id', 'customer_id', 'initial_amount', 'total_paid',
        'balance_due', 'due_date', 'days_overdue', 'last_reminder_at',
        'next_reminder_at', 'responsible_user_id', 'status',
    ];

    protected function casts(): array
    {
        return [
            'initial_amount' => 'integer',
            'total_paid' => 'integer',
            'balance_due' => 'integer',
            'due_date' => 'date',
            'last_reminder_at' => 'datetime',
            'next_reminder_at' => 'datetime',
            'status' => ReceivableStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}
