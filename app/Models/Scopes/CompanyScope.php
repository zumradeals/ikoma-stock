<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Filtre par company_id courante. Une company_id nulle (SUPER_ADMIN ou
     * contexte console) ne filtre rien — voir current_company_id().
     */
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = current_company_id();

        if ($companyId !== null) {
            $builder->where($model->qualifyColumn('company_id'), $companyId);
        }
    }
}
