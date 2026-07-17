<?php

use App\Enums\UserRole;
use App\Jobs\RefreshOverdueStatuses;
use App\Models\Company;
use App\Models\User;
use App\Modules\Invoice\Services\InvoiceService;
use App\Modules\Receivable\Services\ReceivableService;
use Illuminate\Support\Collection;

test('a failure on one company does not prevent other companies from being processed', function () {
    $failingCompany = Company::factory()->create();
    $healthyCompany = Company::factory()->create();

    User::factory()->for($failingCompany)->role(UserRole::ADMIN_COMPANY)->create();
    User::factory()->for($healthyCompany)->role(UserRole::ADMIN_COMPANY)->create();

    $this->mock(InvoiceService::class, function ($mock) use ($failingCompany, $healthyCompany) {
        $mock->shouldReceive('markOverdue')
            ->with($failingCompany->id)
            ->andThrow(new RuntimeException('données corrompues'));

        $mock->shouldReceive('markOverdue')
            ->once()
            ->with($healthyCompany->id)
            ->andReturn(2);
    });

    $this->mock(ReceivableService::class, function ($mock) use ($healthyCompany) {
        $mock->shouldReceive('markOverdue')
            ->once()
            ->with($healthyCompany->id)
            ->andReturn(new Collection);
    });

    app(RefreshOverdueStatuses::class)->handle(app(InvoiceService::class), app(ReceivableService::class));
})->throwsNoExceptions();
