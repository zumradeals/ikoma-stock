<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueInvoicesDetected extends Notification
{
    use Queueable;

    public function __construct(
        protected int $invoicesCount,
        protected int $receivablesCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message(),
            'url' => route('customers.index'),
            'invoices_count' => $this->invoicesCount,
            'receivables_count' => $this->receivablesCount,
        ];
    }

    protected function message(): string
    {
        return "{$this->invoicesCount} facture(s) et {$this->receivablesCount} créance(s) passée(s) en retard.";
    }
}
