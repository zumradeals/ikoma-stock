<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class DeliveryController extends Controller
{
    public function pdf(Delivery $delivery): Response
    {
        $delivery->loadMissing(['invoice.company', 'invoice.sale.customer', 'deliveryLines.product']);

        $pdf = Pdf::loadView('deliveries.pdf', ['delivery' => $delivery]);

        return $pdf->stream("bon-livraison-{$delivery->id}.pdf");
    }
}
