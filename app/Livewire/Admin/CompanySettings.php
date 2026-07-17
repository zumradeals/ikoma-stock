<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CompanySettings extends Component
{
    use WithFileUploads;

    public string $companyName = '';

    public string $companyAddress = '';

    public string $companyPhone = '';

    public string $companyEmail = '';

    public string $companyCurrency = '';

    public string $companyInvoicePrefix = '';

    public string $companyFooterText = '';

    public string $companyPrimaryColor = '';

    public $companyLogo = null;

    public bool $profileSaved = false;

    public bool $showOutletForm = false;

    public ?int $editingOutletId = null;

    public string $outletName = '';

    public string $outletAddress = '';

    public string $outletPhone = '';

    public bool $showWarehouseForm = false;

    public ?int $editingWarehouseId = null;

    public string $warehouseName = '';

    public string $warehouseAddress = '';

    public bool $showUserForm = false;

    public ?int $editingUserId = null;

    public string $userName = '';

    public string $userEmail = '';

    public string $userRole = 'SELLER';

    public ?int $userOutletId = null;

    public ?string $createdUserEmail = null;

    public ?string $createdUserPassword = null;

    public function mount(): void
    {
        $company = $this->company;

        $this->companyName = $company->name;
        $this->companyAddress = $company->address ?? '';
        $this->companyPhone = $company->phone ?? '';
        $this->companyEmail = $company->email ?? '';
        $this->companyCurrency = $company->currency;
        $this->companyInvoicePrefix = $company->invoice_prefix;
        $this->companyFooterText = $company->footer_text ?? '';
        $this->companyPrimaryColor = $company->primary_color ?? '';
    }

    public function getCompanyProperty()
    {
        return auth()->user()->company;
    }

    public function getUsersProperty()
    {
        return User::query()->where('company_id', $this->company->id)->orderBy('name')->get();
    }

    public function getOutletsProperty()
    {
        return Outlet::query()->where('company_id', $this->company->id)->orderBy('name')->get();
    }

    public function getWarehousesProperty()
    {
        return Warehouse::query()->where('company_id', $this->company->id)->orderBy('name')->get();
    }

    public function getCanManageProperty(): bool
    {
        return auth()->user()->role === UserRole::ADMIN_COMPANY;
    }

    public function getAssignableRolesProperty(): array
    {
        return [UserRole::OUTLET_MANAGER, UserRole::SELLER, UserRole::WAREHOUSE_KEEPER];
    }

    // ------------------------------------------------------------------
    // Profil de la société (nom, contact, devise, logo, couleur)
    // ------------------------------------------------------------------

    public function saveCompanyProfile(): void
    {
        $this->authorize('update', $this->company);

        $this->validate([
            'companyName' => 'required|string|max:255',
            'companyAddress' => 'nullable|string|max:255',
            'companyPhone' => 'nullable|string|max:30',
            'companyEmail' => 'nullable|email|max:255',
            'companyCurrency' => 'required|string|size:3',
            'companyInvoicePrefix' => 'required|string|max:10',
            'companyFooterText' => 'nullable|string|max:500',
            'companyPrimaryColor' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'companyLogo' => 'nullable|image|max:2048',
        ]);

        $attributes = [
            'name' => $this->companyName,
            'address' => $this->companyAddress ?: null,
            'phone' => $this->companyPhone ?: null,
            'email' => $this->companyEmail ?: null,
            'currency' => strtoupper($this->companyCurrency),
            'invoice_prefix' => strtoupper($this->companyInvoicePrefix),
            'footer_text' => $this->companyFooterText ?: null,
            'primary_color' => $this->companyPrimaryColor ?: null,
        ];

        if ($this->companyLogo) {
            $attributes['logo_path'] = $this->companyLogo->store('companies/logos', 'public');
        }

        $this->company->update($attributes);

        $this->companyLogo = null;
        $this->profileSaved = true;
    }

    // ------------------------------------------------------------------
    // Points de vente
    // ------------------------------------------------------------------

    public function openOutletForm(?int $outletId = null): void
    {
        $this->reset(['outletName', 'outletAddress', 'outletPhone']);
        $this->editingOutletId = null;

        if ($outletId) {
            $outlet = Outlet::findOrFail($outletId);
            $this->authorize('update', $outlet);

            $this->editingOutletId = $outlet->id;
            $this->outletName = $outlet->name;
            $this->outletAddress = $outlet->address ?? '';
            $this->outletPhone = $outlet->phone ?? '';
        } else {
            $this->authorize('create', Outlet::class);
        }

        $this->showOutletForm = true;
    }

    protected function outletRules(): array
    {
        return [
            'outletName' => 'required|string|max:255',
            'outletAddress' => 'nullable|string|max:255',
            'outletPhone' => 'nullable|string|max:30',
        ];
    }

    public function saveOutlet(): void
    {
        $this->validate($this->outletRules());

        $attributes = [
            'name' => $this->outletName,
            'address' => $this->outletAddress ?: null,
            'phone' => $this->outletPhone ?: null,
        ];

        if ($this->editingOutletId) {
            $outlet = Outlet::findOrFail($this->editingOutletId);
            $this->authorize('update', $outlet);
            $outlet->update($attributes);
        } else {
            $this->authorize('create', Outlet::class);
            Outlet::create($attributes + ['company_id' => $this->company->id, 'is_active' => true]);
        }

        $this->showOutletForm = false;
        $this->editingOutletId = null;
    }

    public function requestToggleOutlet(int $outletId): void
    {
        $outlet = Outlet::findOrFail($outletId);
        $this->authorize('update', $outlet);

        $this->dispatch(
            'confirm-action',
            title: $outlet->is_active ? 'Désactiver ce point de vente' : 'Réactiver ce point de vente',
            message: $outlet->is_active
                ? "Désactiver \"{$outlet->name}\" ?"
                : "Réactiver \"{$outlet->name}\" ?",
            detail: null,
            danger: $outlet->is_active,
            eventName: 'admin.outlet-toggle.confirmed',
            eventParams: ['outletId' => $outletId],
        );
    }

    #[On('admin.outlet-toggle.confirmed')]
    public function toggleOutlet(int $outletId): void
    {
        $outlet = Outlet::findOrFail($outletId);
        $this->authorize('update', $outlet);
        $outlet->update(['is_active' => ! $outlet->is_active]);
    }

    // ------------------------------------------------------------------
    // Dépôts
    // ------------------------------------------------------------------

    public function openWarehouseForm(?int $warehouseId = null): void
    {
        $this->reset(['warehouseName', 'warehouseAddress']);
        $this->editingWarehouseId = null;

        if ($warehouseId) {
            $warehouse = Warehouse::findOrFail($warehouseId);
            $this->authorize('update', $warehouse);

            $this->editingWarehouseId = $warehouse->id;
            $this->warehouseName = $warehouse->name;
            $this->warehouseAddress = $warehouse->address ?? '';
        } else {
            $this->authorize('create', Warehouse::class);
        }

        $this->showWarehouseForm = true;
    }

    protected function warehouseRules(): array
    {
        return [
            'warehouseName' => 'required|string|max:255',
            'warehouseAddress' => 'nullable|string|max:255',
        ];
    }

    public function saveWarehouse(): void
    {
        $this->validate($this->warehouseRules());

        $attributes = [
            'name' => $this->warehouseName,
            'address' => $this->warehouseAddress ?: null,
        ];

        if ($this->editingWarehouseId) {
            $warehouse = Warehouse::findOrFail($this->editingWarehouseId);
            $this->authorize('update', $warehouse);
            $warehouse->update($attributes);
        } else {
            $this->authorize('create', Warehouse::class);
            Warehouse::create($attributes + ['company_id' => $this->company->id, 'is_active' => true]);
        }

        $this->showWarehouseForm = false;
        $this->editingWarehouseId = null;
    }

    public function requestToggleWarehouse(int $warehouseId): void
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $this->authorize('update', $warehouse);

        $this->dispatch(
            'confirm-action',
            title: $warehouse->is_active ? 'Désactiver ce dépôt' : 'Réactiver ce dépôt',
            message: $warehouse->is_active
                ? "Désactiver \"{$warehouse->name}\" ?"
                : "Réactiver \"{$warehouse->name}\" ?",
            detail: null,
            danger: $warehouse->is_active,
            eventName: 'admin.warehouse-toggle.confirmed',
            eventParams: ['warehouseId' => $warehouseId],
        );
    }

    #[On('admin.warehouse-toggle.confirmed')]
    public function toggleWarehouse(int $warehouseId): void
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $this->authorize('update', $warehouse);
        $warehouse->update(['is_active' => ! $warehouse->is_active]);
    }

    // ------------------------------------------------------------------
    // Utilisateurs
    // ------------------------------------------------------------------

    public function openUserForm(?int $userId = null): void
    {
        $this->reset(['userName', 'userEmail', 'userOutletId', 'createdUserEmail', 'createdUserPassword']);
        $this->editingUserId = null;
        $this->userRole = 'SELLER';

        if ($userId) {
            $user = User::where('company_id', $this->company->id)->findOrFail($userId);
            $this->authorize('update', $user);

            $this->editingUserId = $user->id;
            $this->userName = $user->name;
            $this->userEmail = $user->email;
            $this->userRole = $user->role->value;
            $this->userOutletId = $user->outlet_id;
        } else {
            $this->authorize('create', User::class);
        }

        $this->showUserForm = true;
    }

    public function saveUser(): void
    {
        $companyId = $this->company->id;

        $this->validate([
            'userName' => 'required|string|max:255',
            'userEmail' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->where('company_id', $companyId)->ignore($this->editingUserId),
            ],
            'userRole' => Rule::in(array_map(fn (UserRole $r) => $r->value, $this->assignableRoles)),
            'userOutletId' => [
                'nullable',
                Rule::exists('outlets', 'id')->where('company_id', $companyId),
            ],
        ]);

        $outletId = in_array($this->userRole, ['SELLER', 'OUTLET_MANAGER'], true) ? $this->userOutletId : null;

        if ($this->editingUserId) {
            $user = User::where('company_id', $companyId)->findOrFail($this->editingUserId);
            $this->authorize('update', $user);

            $user->update([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'role' => UserRole::from($this->userRole),
                'outlet_id' => $outletId,
            ]);
        } else {
            $this->authorize('create', User::class);

            $password = Str::password(12);

            User::create([
                'company_id' => $companyId,
                'name' => $this->userName,
                'email' => $this->userEmail,
                'password' => Hash::make($password),
                'role' => UserRole::from($this->userRole),
                'outlet_id' => $outletId,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->createdUserEmail = $this->userEmail;
            $this->createdUserPassword = $password;
        }

        $this->showUserForm = false;
        $this->editingUserId = null;
    }

    public function requestToggleUser(int $userId): void
    {
        $user = User::where('company_id', $this->company->id)->findOrFail($userId);
        $this->authorize('update', $user);

        $this->dispatch(
            'confirm-action',
            title: $user->is_active ? 'Désactiver ce compte' : 'Réactiver ce compte',
            message: $user->is_active
                ? "Désactiver l'accès de \"{$user->name}\" ?"
                : "Réactiver l'accès de \"{$user->name}\" ?",
            detail: null,
            danger: $user->is_active,
            eventName: 'admin.user-toggle.confirmed',
            eventParams: ['userId' => $userId],
        );
    }

    #[On('admin.user-toggle.confirmed')]
    public function toggleUser(int $userId): void
    {
        $user = User::where('company_id', $this->company->id)->findOrFail($userId);
        $this->authorize('update', $user);
        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.company-settings');
    }
}
