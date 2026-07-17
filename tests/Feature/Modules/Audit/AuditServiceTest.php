<?php

use App\Models\ActivityLog;
use App\Models\Company;
use App\Modules\Audit\Services\AuditService;

test('log writes an ActivityLog entry with the expected fields', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    $company = Company::factory()->create(['name' => 'Cible Audit']);

    app(AuditService::class)->log($tenant['admin'], 'custom_action', $company, ['a' => 1], ['a' => 2]);

    $log = ActivityLog::query()->latest('id')->first();

    expect($log->action)->toBe('custom_action')
        ->and($log->entity_type)->toBe($company->getMorphClass())
        ->and($log->entity_id)->toBe($company->id)
        ->and($log->user_id)->toBe($tenant['admin']->id)
        ->and($log->old_values)->toBe(['a' => 1])
        ->and($log->new_values)->toBe(['a' => 2]);
});

test('HasAudit automatically logs created/updated/deleted through AuditService', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    $category = \App\Models\Category::factory()->for($tenant['company'])->create();
    expect(ActivityLog::where('entity_type', $category->getMorphClass())->where('entity_id', $category->id)->where('action', 'created')->exists())->toBeTrue();

    $category->update(['name' => 'Nouvelle catégorie']);
    expect(ActivityLog::where('entity_type', $category->getMorphClass())->where('entity_id', $category->id)->where('action', 'updated')->exists())->toBeTrue();

    $categoryId = $category->id;
    $category->delete();
    expect(ActivityLog::where('entity_type', $category->getMorphClass())->where('entity_id', $categoryId)->where('action', 'deleted')->exists())->toBeTrue();
});
