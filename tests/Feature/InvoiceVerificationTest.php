<?php

use App\Models\Invoice;
use Illuminate\Support\Str;

test('a valid token returns the invoice details', function () {
    $tenant  = seedTenant();
    $invoice = validatedInvoice($tenant, 1);

    $this->get(route('invoice.verify', $invoice->verification_token))
        ->assertOk()
        ->assertSee($invoice->number)
        ->assertSee($tenant['company']->name)
        ->assertSee('Facture authentique');
});

test('an invalid token shows error state', function () {
    $this->get(route('invoice.verify', 'totalement-invalide-xyz'))
        ->assertOk()
        ->assertSee('Facture introuvable');
});

test('verification page is accessible without authentication', function () {
    $tenant  = seedTenant();
    $invoice = validatedInvoice($tenant, 1);

    // Not logged in
    $this->get(route('invoice.verify', $invoice->verification_token))
        ->assertOk()
        ->assertSee('Facture authentique');
});

test('invoice model auto-generates a verification token on creation', function () {
    $tenant  = seedTenant();
    $invoice = validatedInvoice($tenant, 1);

    expect($invoice->verification_token)->not->toBeNull()
        ->and(strlen($invoice->verification_token))->toBeGreaterThanOrEqual(16);
});

test('two invoices never share the same verification token', function () {
    $t1 = seedTenant();
    $t2 = seedTenant();

    $i1 = validatedInvoice($t1, 1);
    $i2 = validatedInvoice($t2, 1);

    expect($i1->verification_token)->not->toBe($i2->verification_token);
});

test('verification page shows paid amount', function () {
    $tenant  = seedTenant();
    $invoice = validatedInvoice($tenant, 1);

    $this->get(route('invoice.verify', $invoice->verification_token))
        ->assertOk()
        ->assertSee('Montant payé');
});
