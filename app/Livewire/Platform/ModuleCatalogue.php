<?php

namespace App\Livewire\Platform;

use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class ModuleCatalogue extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $status = 'available';

    public string $pricingType = '';

    public string $price = '';

    public function getModulesProperty()
    {
        return Module::orderBy('status')->orderBy('name')->get();
    }

    public function openCreateForm(): void
    {
        $this->reset(['code', 'name', 'description', 'price']);
        $this->editingId = null;
        $this->status = 'available';
        $this->pricingType = '';
        $this->showForm = true;
    }

    public function openEditForm(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);
        $this->editingId = $module->id;
        $this->code = $module->code;
        $this->name = $module->name;
        $this->description = $module->description ?? '';
        $this->status = $module->status;
        $this->pricingType = $module->pricing_type ?? '';
        $this->price = $module->price ? (string) ($module->price / 100) : '';
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
    }

    protected function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('modules', 'code')->ignore($this->editingId)],
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status'      => Rule::in(['available', 'planned', 'deprecated']),
            'pricingType' => ['nullable', Rule::in(['free', 'paid', ''])],
            'price'       => 'nullable|numeric|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $attributes = [
            'name'         => $this->name,
            'description'  => $this->description ?: null,
            'status'       => $this->status,
            'pricing_type' => $this->pricingType ?: null,
            'price'        => $this->price !== '' ? (int) round((float) $this->price * 100) : null,
        ];

        if ($this->editingId) {
            Module::findOrFail($this->editingId)->update($attributes);
        } else {
            Module::create($attributes + ['code' => $this->code]);
        }

        $this->showForm = false;
        $this->editingId = null;
    }

    public function requestDelete(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);

        $this->dispatch(
            'confirm-action',
            title: 'Supprimer ce module',
            message: "Supprimer \"{$module->name}\" du catalogue ?",
            detail: 'Cette action est irréversible. Les activations existantes en société seront supprimées.',
            danger: true,
            eventName: 'platform.module-delete.confirmed',
            eventParams: ['moduleId' => $moduleId],
        );
    }

    #[On('platform.module-delete.confirmed')]
    public function delete(int $moduleId): void
    {
        Module::findOrFail($moduleId)->delete();
    }

    public function render()
    {
        return view('livewire.platform.module-catalogue');
    }
}
