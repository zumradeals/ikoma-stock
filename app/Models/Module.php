<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = ['code', 'name', 'description', 'status', 'pricing_type', 'price'];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_modules')
            ->withPivot(['enabled', 'enabled_at', 'enabled_by'])
            ->withTimestamps();
    }
}
