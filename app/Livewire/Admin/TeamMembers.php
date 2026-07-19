<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class TeamMembers extends Component
{
    public bool $showForm = false;

    public string $name = '';

    public string $phone = '';

    public string $role = '';

    public ?string $generatedPin = null;

    public ?string $createdMemberName = null;

    /** Roles that can be created / managed (ADMIN_COMPANY is shown read-only) */
    private const MANAGEABLE_ROLES = [
        UserRole::OUTLET_MANAGER,
        UserRole::SELLER,
        UserRole::WAREHOUSE_KEEPER,
    ];

    public function getTeamProperty()
    {
        return User::where('company_id', auth()->user()->company_id)
            ->where('role', '!=', UserRole::SUPER_ADMIN->value)
            ->orderByRaw("CASE role
                WHEN 'ADMIN_COMPANY' THEN 1
                WHEN 'OUTLET_MANAGER' THEN 2
                WHEN 'SELLER' THEN 3
                WHEN 'WAREHOUSE_KEEPER' THEN 4
                ELSE 5 END")
            ->orderBy('name')
            ->get();
    }

    public function openForm(): void
    {
        $this->reset(['name', 'phone', 'role', 'generatedPin', 'createdMemberName']);
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->generatedPin = null;
    }

    public function save(): void
    {
        $this->validate([
            'name'  => 'required|string|max:255',
            'phone' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    $digits = preg_replace('/\D/', '', $value);
                    if (strlen($digits) < 6) {
                        $fail('Le numéro de téléphone est invalide.');
                    }
                    $normalized = str_starts_with($digits, '225') ? '+' . $digits : '+225' . $digits;
                    if (User::where('phone', $normalized)->exists()) {
                        $fail('Ce numéro est déjà utilisé.');
                    }
                },
            ],
            'role' => ['required', function ($attribute, $value, $fail) {
                $allowed = array_map(fn ($r) => $r->value, self::MANAGEABLE_ROLES);
                if (! in_array($value, $allowed, true)) {
                    $fail('Rôle invalide.');
                }
            }],
        ]);

        $digits = preg_replace('/\D/', '', $this->phone);
        $phone  = str_starts_with($digits, '225') ? '+' . $digits : '+225' . $digits;

        $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $creator = auth()->user();

        User::create([
            'company_id' => $creator->company_id,
            'name'       => $this->name,
            'email'      => preg_replace('/\D/', '', $phone) . '@noreply.local',
            'phone'      => $phone,
            'password'   => Hash::make($pin),
            'role'       => $this->role,
            'outlet_id'  => $this->role === UserRole::SELLER->value ? $creator->outlet_id : null,
            'is_active'  => true,
        ]);

        $this->generatedPin    = $pin;
        $this->createdMemberName = $this->name;
        $this->showForm        = false;
        $this->reset(['name', 'phone', 'role']);
    }

    public function toggleActive(int $userId): void
    {
        $member = User::where('id', $userId)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        // Protect ADMIN_COMPANY accounts from being toggled
        if ($member->role === UserRole::ADMIN_COMPANY) {
            return;
        }

        $member->update(['is_active' => ! $member->is_active]);
    }

    public function dismissPin(): void
    {
        $this->generatedPin    = null;
        $this->createdMemberName = null;
    }

    public function render()
    {
        return view('livewire.admin.team-members');
    }
}
