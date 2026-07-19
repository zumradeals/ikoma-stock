<?php

use App\Enums\CustomerType;
use App\Enums\QuoteStatus;
use App\Models\Module;
use App\Models\Quote;
use App\Models\StockLevel;
use App\Modules\Quote\Services\QuoteService;

// ── helpers ──────────────────────────────────────────────────────────────────

function activateQuotes(array $tenant): void
{
    $module = Module::where('code', 'quotes')->firstOrFail();
    $tenant['company']->modules()->syncWithoutDetaching([
        $module->id => ['enabled' => true, 'enabled_at' => now(), 'enabled_by' => null],
    ]);
    $tenant['company']->moduleCache = [];
}

function makeQuote(array $tenant, QuoteService $svc, int $qty = 2): Quote
{
    $quote = $svc->create([
        'company_id'    => $tenant['company']->id,
        'outlet_id'     => $tenant['outlet']->id,
        'user_id'       => $tenant['admin']->id,
        'customer_id'   => $tenant['customer']->id,
        'customer_type' => CustomerType::REGISTERED,
    ]);

    $svc->addLine($quote, $tenant['product'], $qty);

    return $quote->fresh();
}

// ── catalogue ─────────────────────────────────────────────────────────────────

test('quotes module is available in catalogue', function () {
    $module = Module::where('code', 'quotes')->first();

    expect($module)->not->toBeNull()
        ->and($module->status)->toBe('available');
});

// ── création sans impact stock ────────────────────────────────────────────────

test('creating a quote does not reserve or decrement stock', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $stockBefore = StockLevel::where('product_id', $tenant['product']->id)
        ->where('location_id', $tenant['outlet']->id)
        ->first();

    makeQuote($tenant, $svc, 10);

    $stockAfter = $stockBefore->fresh();

    expect($stockAfter->quantity_physical)->toBe($stockBefore->quantity_physical)
        ->and($stockAfter->quantity_reserved)->toBe(0);
});

test('quote total_amount is computed from lines without touching stock', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $quote = makeQuote($tenant, $svc, 3);

    expect($quote->total_amount)->toBe($tenant['product']->sale_price * 3)
        ->and($quote->status)->toBe(QuoteStatus::DRAFT);
});

// ── remise ────────────────────────────────────────────────────────────────────

test('applying a percentage discount updates discount_amount correctly', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $quote = makeQuote($tenant, $svc, 4);
    $svc->applyDiscount($quote, 0, 10);
    $quote = $quote->fresh();

    $expected = (int) round($quote->total_amount * 10 / 100);
    expect($quote->discount_amount)->toBe($expected)
        ->and($quote->discount_percentage)->toBe(10);
});

// ── transitions ───────────────────────────────────────────────────────────────

test('quote can be marked sent then accepted', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $quote = makeQuote($tenant, $svc);

    $svc->markSent($quote);
    expect($quote->fresh()->status)->toBe(QuoteStatus::SENT);

    $svc->markAccepted($quote->fresh());
    expect($quote->fresh()->status)->toBe(QuoteStatus::ACCEPTED);
});

test('quote can be marked refused from draft', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $quote = makeQuote($tenant, $svc);
    $svc->markRefused($quote);

    expect($quote->fresh()->status)->toBe(QuoteStatus::REFUSED);
});

test('a refused quote cannot be sent', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $quote = makeQuote($tenant, $svc);
    $svc->markRefused($quote);

    expect(fn () => $svc->markSent($quote->fresh()))
        ->toThrow(App\Exceptions\Business\SaleValidationForbiddenException::class);
});

// ── conversion ────────────────────────────────────────────────────────────────

test('converting a quote creates a sale and an invoice', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $this->actingAs($tenant['admin']);

    $quote   = makeQuote($tenant, $svc, 2);
    $invoice = $svc->convert($quote);

    $quote = $quote->fresh();

    expect($quote->status)->toBe(QuoteStatus::CONVERTED)
        ->and($quote->converted_sale_id)->not->toBeNull()
        ->and($invoice)->toBeInstanceOf(App\Models\Invoice::class)
        ->and($invoice->total_amount)->toBe($quote->total_amount);
});

test('stock is reserved only at conversion, not at quote creation', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $stockRow = StockLevel::where('product_id', $tenant['product']->id)
        ->where('location_id', $tenant['outlet']->id)
        ->first();

    // Create quote — no stock change
    $quote = makeQuote($tenant, $svc, 5);
    expect($stockRow->fresh()->quantity_reserved)->toBe(0);

    // Convert — stock is now reserved
    $this->actingAs($tenant['admin']);
    $svc->convert($quote);

    expect($stockRow->fresh()->quantity_reserved)->toBe(5);
});

test('converted quote records converted_sale_id and converted_by', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $this->actingAs($tenant['admin']);

    $quote = makeQuote($tenant, $svc, 1);
    $svc->convert($quote);

    $quote = $quote->fresh();

    expect($quote->converted_sale_id)->not->toBeNull()
        ->and($quote->converted_by)->toBe($tenant['admin']->id)
        ->and($quote->converted_at)->not->toBeNull();
});

test('discount is preserved when converting quote to sale', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $this->actingAs($tenant['admin']);

    $quote = makeQuote($tenant, $svc, 4);
    $svc->applyDiscount($quote, 0, 20); // 20% discount
    $quote = $quote->fresh();

    $invoice = $svc->convert($quote);

    $expectedNet = (int) round($quote->total_amount * 0.80);
    expect($invoice->total_amount)->toBe($expectedNet);
});

test('a converted quote cannot be converted again', function () {
    $tenant = seedTenant(50);
    $svc    = app(QuoteService::class);

    $this->actingAs($tenant['admin']);

    $quote = makeQuote($tenant, $svc, 1);
    $svc->convert($quote);

    expect(fn () => $svc->convert($quote->fresh()))
        ->toThrow(App\Exceptions\Business\SaleValidationForbiddenException::class);
});

// ── gate module ───────────────────────────────────────────────────────────────

test('quotes routes are blocked when module is not enabled', function () {
    $tenant = seedTenant();
    $this->actingAs($tenant['admin']);

    $response = $this->get(route('quotes.index'));

    $response->assertRedirect(route('app.dashboard'));
});

test('quotes routes are accessible when module is enabled', function () {
    $tenant = seedTenant();
    activateQuotes($tenant);

    $this->actingAs($tenant['admin']);

    $response = $this->get(route('quotes.index'));

    $response->assertOk();
});
