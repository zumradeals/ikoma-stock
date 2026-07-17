<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StockController extends Controller
{
    public function exportPdf(Request $request): Response
    {
        $search = (string) $request->query('search', '');

        $products = Product::query()
            ->where('is_active', true)
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $levels = StockLevel::query()->get()->groupBy('product_id');
        $warehouses = Warehouse::query()->orderBy('name')->get();
        $outlets = Outlet::query()->orderBy('name')->get();

        $rows = $products->map(fn (Product $product) => [
            'product' => $product,
            'byLocation' => $levels->get($product->id, collect())->keyBy(
                fn (StockLevel $l) => $l->location_type->value.':'.$l->location_id
            ),
        ]);

        $pdf = Pdf::loadView('stock.export', compact('rows', 'warehouses', 'outlets'));

        return $pdf->stream('etat-du-stock.pdf');
    }
}
