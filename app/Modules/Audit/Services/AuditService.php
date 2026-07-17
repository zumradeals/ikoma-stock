<?php

namespace App\Modules\Audit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Point de passage unique pour toute écriture dans ActivityLogs. Appelé
 * automatiquement par App\Traits\HasAudit sur created/updated/deleted, et
 * peut aussi être appelé directement par les services pour des actions
 * sensibles qui ne correspondent pas à un simple événement Eloquent
 * (validation de vente, annulation de facture, verrouillage de point de
 * journée...).
 */
class AuditService
{
    public function log(?User $user, string $action, Model $entity, array $oldValues = [], array $newValues = []): void
    {
        $request = request();

        ActivityLog::create([
            'company_id' => $entity->company_id ?? current_company_id(),
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'device_info' => $request?->userAgent(),
            'ip_address' => $request?->ip(),
            'session_id' => $request?->hasSession() ? $request->session()->getId() : null,
        ]);
    }
}
