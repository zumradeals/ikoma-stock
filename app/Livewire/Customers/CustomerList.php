<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CustomerList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public bool $showCreateForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $neighborhoodCity = '';

    public string $creditLimit = '';

    public string $notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getCustomersProperty()
    {
        return Customer::query()
            ->withSum(['receivables as open_debt' => fn ($q) => $q->where('status', '!=', 'PAID')], 'balance_due')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);
    }

    public function openCreateForm(): void
    {
        $this->authorize('create', Customer::class);

        $this->editingId = null;
        $this->reset(['name', 'phone', 'address', 'neighborhoodCity', 'creditLimit', 'notes']);
        $this->showCreateForm = true;
    }

    public function openEditForm(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $this->authorize('update', $customer);

        $this->editingId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->neighborhoodCity = $customer->neighborhood_city ?? '';
        $this->creditLimit = $customer->credit_limit !== null ? (string) ($customer->credit_limit / 100) : '';
        $this->notes = $customer->notes ?? '';
        $this->showCreateForm = true;
    }

    protected function customerRules(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'name' => 'required|string|max:255',
            'phone' => [
                'nullable', 'string', 'max:30',
                Rule::unique('customers', 'phone')->where('company_id', $companyId)->ignore($this->editingId),
            ],
            'address' => 'nullable|string|max:255',
            'neighborhoodCity' => 'nullable|string|max:255',
            'creditLimit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function saveCustomer(): void
    {
        $this->validate($this->customerRules());

        $attributes = [
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'neighborhood_city' => $this->neighborhoodCity ?: null,
            'credit_limit' => $this->creditLimit !== '' ? (int) round(((float) $this->creditLimit) * 100) : null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $customer = Customer::findOrFail($this->editingId);
            $this->authorize('update', $customer);
            $customer->update($attributes);
        } else {
            $this->authorize('create', Customer::class);
            Customer::create($attributes + ['company_id' => auth()->user()->company_id, 'is_active' => true]);
        }

        $this->showCreateForm = false;
        $this->editingId = null;
        $this->resetPage();
    }

    public function requestToggle(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $this->authorize('update', $customer);

        $this->dispatch(
            'confirm-action',
            title: $customer->is_active ? 'Désactiver ce client' : 'Réactiver ce client',
            message: $customer->is_active
                ? "Désactiver « {$customer->name} » ?"
                : "Réactiver « {$customer->name} » ?",
            detail: null,
            danger: $customer->is_active,
            eventName: 'customers.toggle.confirmed',
            eventParams: ['customerId' => $customerId],
        );
    }

    #[On('customers.toggle.confirmed')]
    public function toggle(int $customerId): void
    {
        $customer = Customer::findOrFail($customerId);
        $this->authorize('update', $customer);
        $customer->update(['is_active' => ! $customer->is_active]);
    }

    public function render()
    {
        return view('livewire.customers.customer-list', [
            'customers' => $this->customers,
        ]);
    }
}
