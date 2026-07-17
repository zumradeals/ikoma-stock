<?php

namespace App\Jobs;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Modules\Invoice\Services\InvoiceService;
use App\Modules\Receivable\Services\ReceivableService;
use App\Notifications\OverdueInvoicesDetected;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Job quotidien (00:01, voir routes/console.php) : recalcule les statuts de
 * retard (factures + créances) entreprise par entreprise. Chaque entreprise
 * est isolée dans son propre try/catch + transaction : une erreur sur l'une
 * (données corrompues, contrainte violée...) ne doit jamais empêcher le
 * traitement des autres.
 */
class RefreshOverdueStatuses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function handle(InvoiceService $invoiceService, ReceivableService $receivableService): void
    {
        $companies = Company::query()->get(['id', 'name']);
        $totalInvoices = 0;
        $totalReceivables = 0;
        $failures = 0;

        foreach ($companies as $company) {
            try {
                $result = DB::transaction(function () use ($company, $invoiceService, $receivableService) {
                    $invoicesUpdated = $invoiceService->markOverdue($company->id);
                    $receivables = $receivableService->markOverdue($company->id);
                    $receivablesUpdated = $receivables->count();

                    if ($invoicesUpdated > 0 || $receivablesUpdated > 0) {
                        $this->notifyCompany($company, $invoicesUpdated, $receivablesUpdated);
                    }

                    return ['invoices' => $invoicesUpdated, 'receivables' => $receivablesUpdated];
                });

                $totalInvoices += $result['invoices'];
                $totalReceivables += $result['receivables'];

                Log::info('RefreshOverdueStatuses: entreprise traitée', [
                    'company_id' => $company->id,
                    'invoices_updated' => $result['invoices'],
                    'receivables_updated' => $result['receivables'],
                ]);
            } catch (Throwable $e) {
                $failures++;

                Log::error('RefreshOverdueStatuses: échec sur une entreprise', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('RefreshOverdueStatuses: exécution terminée', [
            'companies_processed' => $companies->count(),
            'companies_failed' => $failures,
            'invoices_updated' => $totalInvoices,
            'receivables_updated' => $totalReceivables,
        ]);
    }

    /**
     * Notifie les responsables de l'entreprise (ADMIN_COMPANY/OUTLET_MANAGER)
     * — les seuls rôles concernés par le suivi des impayés.
     */
    protected function notifyCompany(Company $company, int $invoicesUpdated, int $receivablesUpdated): void
    {
        $recipients = User::query()
            ->where('company_id', $company->id)
            ->whereIn('role', [UserRole::ADMIN_COMPANY->value, UserRole::OUTLET_MANAGER->value])
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new OverdueInvoicesDetected($invoicesUpdated, $receivablesUpdated));
    }
}
