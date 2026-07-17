<?php

namespace App\Livewire\Customers;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Modules\Customer\Services\CustomerService;
use Livewire\Component;

class CustomerAlert extends Component
{
    public Customer $customer;

    public $receivables;

    public $undeliveredInvoices;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;

        $dues = app(CustomerService::class)->checkOutstandingDues($customer);
        $this->receivables = $dues['receivables'];
        $this->undeliveredInvoices = $dues['undelivered_invoices'];
    }

    public function getHasDuesProperty(): bool
    {
        return $this->receivables->isNotEmpty() || $this->undeliveredInvoices->isNotEmpty();
    }

    public function getCanBlockProperty(): bool
    {
        return in_array(auth()->user()->role, [UserRole::ADMIN_COMPANY, UserRole::OUTLET_MANAGER], true);
    }

    public function continueAnyway(): void
    {
        $this->dispatch('customer-alert.continue');
    }

    public function block(): void
    {
        $this->dispatch('customer-alert.block');
    }

    public function render()
    {
        return view('livewire.customers.customer-alert');
    }
}
