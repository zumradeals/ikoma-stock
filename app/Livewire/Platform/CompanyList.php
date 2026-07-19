<?php

namespace App\Livewire\Platform;

use App\Enums\SubscriptionPlan;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class CompanyList extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';  // '' = tous, 'active', 'suspended'

    public bool $showCreateForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $address = '';

    public string $phone = '';

    public string $email = '';

    public string $currency = 'XOF';

    public string $invoicePrefix = 'FAC';

    public string $subscriptionPlan = 'DECOUVERTE';

    public string $adminName = '';

    public string $adminEmail = '';

    public ?string $createdAdminEmail = null;

    public ?string $createdPassword = null;

    public function getCompaniesProperty()
    {
        return Company::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'suspended', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch(): void
    {
        // Reset handled by Url attribute on re-render
    }

    public function openCreateForm(): void
    {
        $this->authorize('create', Company::class);

        $this->editingId = null;
        $this->reset(['name', 'address', 'phone', 'email', 'adminName', 'adminEmail', 'createdAdminEmail', 'createdPassword']);
        $this->currency = 'XOF';
        $this->invoicePrefix = 'FAC';
        $this->subscriptionPlan = SubscriptionPlan::DECOUVERTE->value;
        $this->showCreateForm = true;
    }

    public function openEditForm(int $companyId): void
    {
        $company = Company::findOrFail($companyId);
        $this->authorize('update', $company);

        $this->editingId          = $company->id;
        $this->name               = $company->name;
        $this->address            = $company->address ?? '';
        $this->phone              = $company->phone ?? '';
        $this->email              = $company->email ?? '';
        $this->currency           = $company->currency;
        $this->invoicePrefix      = $company->invoice_prefix;
        $this->subscriptionPlan   = $company->subscription_plan?->value ?? SubscriptionPlan::DECOUVERTE->value;
        $this->showCreateForm     = true;
    }

    public function cancelCreate(): void
    {
        $this->showCreateForm = false;
        $this->editingId = null;
    }

    protected function companyRules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:255',
            'currency'         => 'required|string|size:3',
            'invoicePrefix'    => 'required|string|max:10',
            'subscriptionPlan' => ['required', \Illuminate\Validation\Rule::enum(SubscriptionPlan::class)],
        ];
    }

    public function updateCompany(): void
    {
        $company = Company::findOrFail($this->editingId);
        $this->authorize('update', $company);

        $this->validate($this->companyRules());

        $company->update([
            'name'              => $this->name,
            'address'           => $this->address ?: null,
            'phone'             => $this->phone ?: null,
            'email'             => $this->email ?: null,
            'currency'          => strtoupper($this->currency),
            'invoice_prefix'    => strtoupper($this->invoicePrefix),
            'subscription_plan' => $this->subscriptionPlan,
        ]);

        $this->showCreateForm = false;
        $this->editingId = null;
    }

    public function createCompany(): void
    {
        $this->authorize('create', Company::class);

        $this->validate($this->companyRules() + [
            'adminName'  => 'required|string|max:255',
            'adminEmail' => 'required|email|max:255',
        ]);

        $password = Str::password(12);

        DB::transaction(function () use ($password) {
            $company = Company::create([
                'name'              => $this->name,
                'address'           => $this->address ?: null,
                'phone'             => $this->phone ?: null,
                'email'             => $this->email ?: null,
                'currency'          => strtoupper($this->currency),
                'invoice_prefix'    => strtoupper($this->invoicePrefix),
                'subscription_plan' => $this->subscriptionPlan,
                'is_active'         => true,
            ]);

            User::create([
                'company_id'        => $company->id,
                'name'              => $this->adminName,
                'email'             => $this->adminEmail,
                'password'          => Hash::make($password),
                'role'              => UserRole::ADMIN_COMPANY,
                'is_active'         => true,
                'email_verified_at' => now(),
            ]);
        });

        $this->createdAdminEmail = $this->adminEmail;
        $this->createdPassword   = $password;
        $this->showCreateForm    = false;
    }

    public function requestToggle(int $companyId): void
    {
        $company = Company::findOrFail($companyId);
        $this->authorize('update', $company);

        $suspending = $company->is_active;

        $this->dispatch(
            'confirm-action',
            title: $suspending ? 'Suspendre cette société' : 'Réactiver cette société',
            message: $suspending
                ? "Suspendre l'accès de \"{$company->name}\" ?"
                : "Réactiver l'accès de \"{$company->name}\" ?",
            detail: null,
            danger: $suspending,
            eventName: 'platform.company-toggle.confirmed',
            eventParams: ['companyId' => $companyId],
        );
    }

    #[On('platform.company-toggle.confirmed')]
    public function confirmed(int $companyId): void
    {
        $company = Company::findOrFail($companyId);
        $this->authorize('update', $company);

        if ($company->is_active) {
            $company->update([
                'is_active'        => false,
                'suspended_at'     => now(),
                'suspended_reason' => 'Suspendue depuis la plateforme',
            ]);
        } else {
            $company->update(['is_active' => true, 'suspended_at' => null, 'suspended_reason' => null]);
        }
    }

    /** Renvoie le premier ADMIN_COMPANY actif d'une société (pour le bouton support). */
    public function adminUserFor(Company $company): ?User
    {
        return $company->users()
            ->where('role', UserRole::ADMIN_COMPANY->value)
            ->where('is_active', true)
            ->first();
    }

    public function render()
    {
        return view('livewire.platform.company-list');
    }
}
