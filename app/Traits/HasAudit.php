<?php

namespace App\Traits;

use App\Modules\Audit\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait HasAudit
{
    public static function bootHasAudit(): void
    {
        static::created(function (Model $model) {
            static::recordActivity($model, 'created', [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $old = collect($changes)
                ->keys()
                ->mapWithKeys(fn ($key) => [$key => $model->getOriginal($key)])
                ->all();

            static::recordActivity($model, 'updated', $old, $changes);
        });

        static::deleted(function (Model $model) {
            static::recordActivity($model, 'deleted', $model->getAttributes(), []);
        });
    }

    protected static function recordActivity(Model $model, string $action, array $oldValues, array $newValues): void
    {
        app(AuditService::class)->log(auth()->user(), $action, $model, $oldValues, $newValues);
    }
}
