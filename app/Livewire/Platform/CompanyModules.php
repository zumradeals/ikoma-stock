<?php

namespace App\Livewire\Platform;

use App\Models\Company;
use App\Models\Module;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CompanyModules extends Component
{
    public function getCompaniesProperty()
    {
        return Company::orderBy('name')->get();
    }

    public function getModulesProperty()
    {
        return Module::where('status', 'available')->orderBy('name')->get();
    }

    public function toggle(int $companyId, int $moduleId): void
    {
        $company = Company::findOrFail($companyId);
        $module  = Module::findOrFail($moduleId);

        $pivot = $company->modules()->where('module_id', $moduleId)->first()?->pivot;

        if ($pivot) {
            $nowEnabled = ! (bool) $pivot->enabled;
            $company->modules()->updateExistingPivot($moduleId, [
                'enabled'    => $nowEnabled,
                'enabled_at' => $nowEnabled ? now() : null,
                'enabled_by' => $nowEnabled ? auth()->id() : null,
            ]);
        } else {
            $company->modules()->attach($moduleId, [
                'enabled'    => true,
                'enabled_at' => now(),
                'enabled_by' => auth()->id(),
            ]);
        }

        // Flush instance cache so hasModule() reflects the change immediately
        $company->moduleCache = [];
    }

    public function isEnabled(Company $company, int $moduleId): bool
    {
        return (bool) $company->modules()
            ->where('module_id', $moduleId)
            ->wherePivot('enabled', true)
            ->exists();
    }

    public function render()
    {
        return view('livewire.platform.company-modules');
    }
}
