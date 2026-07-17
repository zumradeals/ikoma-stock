<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de livraison {{ $delivery->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .header { display: table; width: 100%; margin-bottom: 16px; }
        .header .col { display: table-cell; vertical-align: top; width: 50%; }
    </style>
</head>
<body>
    <div class="header">
        <div class="col">
            <h1>{{ $delivery->invoice->company->name }}</h1>
            <div class="muted">{{ $delivery->invoice->company->address }}</div>
            <div class="muted">{{ $delivery->invoice->company->phone }}</div>
        </div>
        <div class="col text-right">
            <h1>Bon de livraison</h1>
            <div class="muted">Facture {{ $delivery->invoice->number }}</div>
            <div class="muted">Livré le {{ $delivery->delivered_at->format('d/m/Y H:i') }}</div>
            <div class="muted">Client : {{ $delivery->invoice->sale->customer->name ?? 'Client de passage' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-right">Quantité livrée</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($delivery->deliveryLines as $line)
                <tr>
                    <td>{{ $line->product->name }}</td>
                    <td class="text-right">{{ $line->quantity_delivered }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($delivery->note)
        <p class="muted" style="margin-top: 24px;">{{ $delivery->note }}</p>
    @endif
</body>
</html>
