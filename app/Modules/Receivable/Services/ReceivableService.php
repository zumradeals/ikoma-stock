<?php

namespace App\Modules\Receivable\Services;

use App\Enums\ReceivableStatus;
use App\Models\Invoice;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReceivableService
{
    /**
     * Crée ou met à jour la créance associée à une facture. Ne fait rien
     * si la vente n'a pas de client identifié (client passager) : une
     * créance sans customer_id n'a pas de sens (colonne NOT NULL).
     */
    public function syncFromInvoice(Invoice $invoice): ?Receivable
    {
        $sale = $invoice->sale;

        if (! $sale->customer_id) {
            return null;
        }

        $receivable = Receivable::firstOrNew(['invoice_id' => $invoice->id]);

        $receivable->fill([
            'company_id' => $invoice->company_id,
            'customer_id' => $sale->customer_id,
            'initial_amount' => $receivable->exists ? $receivable->initial_amount : $invoice->total_amount,
            'total_paid' => $invoice->paid_amount,
            'balance_due' => $invoice->balance_due,
            'due_date' => $invoice->due_date,
            'days_overdue' => static::daysOverdueFor($invoice->due_date),
            'status' => $this->resolveStatus($invoice),
        ]);

        $receivable->save();

        return $receivable;
    }

    /**
     * Job quotidien : bascule en OVERDUE toute créance impayée dont
     * l'échéance est dépassée. $companyId restreint le traitement à une
     * seule entreprise (RefreshOverdueStatuses les isole les unes des
     * autres) ; null traite toutes les entreprises.
     */
    public function markOverdue(?int $companyId = null): Collection
    {
        $receivables = Receivable::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('status', '!=', ReceivableStatus::PAID->value)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        $receivables->each(function (Receivable $receivable) {
            $receivable->update([
                'status' => ReceivableStatus::OVERDUE,
                'days_overdue' => $this->calculateDaysOverdue($receivable),
            ]);
        });

        return $receivables;
    }

    public function dueToday(): Collection
    {
        return Receivable::query()
            ->where('status', '!=', ReceivableStatus::PAID->value)
            ->whereDate('due_date', now()->toDateString())
            ->get();
    }

    public function calculateDaysOverdue(Receivable $receivable): int
    {
        return static::daysOverdueFor($receivable->due_date);
    }

    protected function resolveStatus(Invoice $invoice): ReceivableStatus
    {
        if ($invoice->balance_due <= 0) {
            return ReceivableStatus::PAID;
        }

        if ($invoice->due_date && $invoice->due_date->isPast()) {
            return ReceivableStatus::OVERDUE;
        }

        return $invoice->paid_amount > 0 ? ReceivableStatus::PARTIAL : ReceivableStatus::OPEN;
    }

    protected static function daysOverdueFor(?Carbon $dueDate): int
    {
        if (! $dueDate || $dueDate->isFuture()) {
            return 0;
        }

        return (int) $dueDate->diffInDays(now());
    }
}
