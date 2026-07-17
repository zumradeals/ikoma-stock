<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (Model $model) {
            if (empty($model->company_id) && ($companyId = current_company_id()) !== null) {
                $model->company_id = $companyId;
            }
        });
    }
}
