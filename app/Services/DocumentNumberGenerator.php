<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberGenerator
{
    /**
     * Génère un numéro séquentiel unique PREFIX-YYYYMM-NNNN par société et
     * par mois, pour un type de document donné ('sales', 'invoices',
     * 'transfers'...). Le compteur est verrouillé (SELECT ... FOR UPDATE)
     * dans une transaction pour éviter toute collision en cas de créations
     * concurrentes, y compris pour le premier document de la période.
     */
    public function generate(string $documentType, int $companyId, string $prefix, ?\DateTimeInterface $date = null): string
    {
        $date ??= now();
        $period = $date->format('Ym');

        return DB::transaction(function () use ($documentType, $companyId, $period, $prefix) {
            DB::table('document_sequences')->insertOrIgnore([
                'company_id' => $companyId,
                'document_type' => $documentType,
                'period' => $period,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('document_sequences')
                ->where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            $nextNumber = $sequence->last_number + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update(['last_number' => $nextNumber, 'updated_at' => now()]);

            return sprintf('%s-%s-%04d', $prefix, $period, $nextNumber);
        });
    }
}
