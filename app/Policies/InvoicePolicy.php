<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Ne réutilise pas GrantsSuperAdmin : 'delete' doit rester refusé même
     * pour un SUPER_ADMIN (aucun rôle ne peut supprimer une facture).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($ability === 'delete') {
            return false;
        }

        return $user->role === UserRole::SUPER_ADMIN ? true : null;
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true);
    }

    /**
     * Toujours refusé — une facture ne se supprime jamais (voir
     * Invoice::booted() et InvoiceDeletionForbiddenException), cette
     * méthode existe pour que `$user->can('delete', $invoice)` reflète la
     * même règle côté autorisation.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }
}
