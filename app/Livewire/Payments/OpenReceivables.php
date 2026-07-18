<?php

namespace App\Livewire\Payments;

use App\Enums\ReceivableStatus;
use App\Models\Receivable;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class OpenReceivables extends Component
{
    public string $search = '';

    public function getReceivablesProperty()
    {
        return Receivable::query()
            ->with(['customer', 'invoice.sale'])
            ->where('status', '!=', ReceivableStatus::PAID->value)
            ->when($this->search !== '', function ($q) {
                $q->whereHas('customer', fn ($c) => $c
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                );
            })
            ->orderByDesc('days_overdue')
            ->orderByDesc('balance_due')
            ->get();
    }

    public function getTotalDueProperty(): int
    {
        return $this->receivables->sum('balance_due');
    }

    public function getDistinctCustomersCountProperty(): int
    {
        return $this->receivables->pluck('customer_id')->filter()->unique()->count();
    }

    public function render()
    {
        return view('livewire.payments.open-receivables');
    }
}
