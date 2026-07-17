<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Modules\Invoice\Services\InvoiceService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice, InvoiceService $invoices): StreamedResponse
    {
        $path = $invoices->generatePdf($invoice);

        return Storage::download($path, "{$invoice->number}.pdf");
    }
}
