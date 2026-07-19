<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Response;

class InvoiceVerificationController extends Controller
{
    public function __invoke(string $token): Response
    {
        $invoice = Invoice::with(['company', 'sale'])
            ->where('verification_token', $token)
            ->first();

        return response()->view('verification.invoice', [
            'invoice' => $invoice,
        ]);
    }
}
