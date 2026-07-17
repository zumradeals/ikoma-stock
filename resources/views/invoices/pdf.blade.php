<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .totals td { border: none; padding: 2px 8px; }
        .header { display: table; width: 100%; margin-bottom: 16px; }
        .header .col { display: table-cell; vertical-align: top; width: 50%; }
        .logo { max-height: 48px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="col">
            @if ($invoice->company->logo_path && \Illuminate\Support\Facades\Storage::exists($invoice->company->logo_path))
                <img class="logo" src="{{ \Illuminate\Support\Facades\Storage::path($invoice->company->logo_path) }}" alt="{{ $invoice->company->name }}">
            @endif
            <h1>{{ $invoice->company->name }}</h1>
            <div class="muted">{{ $invoice->company->address }}</div>
            <div class="muted">{{ $invoice->company->phone }}</div>
        </div>
        <div class="col text-right">
            <h1>Facture {{ $invoice->number }}</h1>
            <div class="muted">Émise le {{ $invoice->issue_date->format('d/m/Y') }}</div>
            @if ($invoice->due_date)
                <div class="muted">Échéance le {{ $invoice->due_date->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-right">Quantité</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Remise</th>
                <th class="text-right">Total ligne</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->sale->saleLines as $line)
                <tr>
                    <td>{{ $line->product->name }}</td>
                    <td class="text-right">{{ $line->quantity }}</td>
                    <td class="text-right">{{ number_format($line->unit_price / 100, 0, ',', ' ') }} {{ $invoice->company->currency }}</td>
                    <td class="text-right">{{ number_format($line->line_discount / 100, 0, ',', ' ') }} {{ $invoice->company->currency }}</td>
                    <td class="text-right">{{ number_format($line->line_total / 100, 0, ',', ' ') }} {{ $invoice->company->currency }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="text-right" style="width: 85%">Total facture</td>
            <td class="text-right">{{ number_format($invoice->total_amount / 100, 0, ',', ' ') }} {{ $invoice->company->currency }}</td>
        </tr>
        <tr>
            <td class="text-right">Montant payé</td>
            <td class="text-right">{{ number_format($invoice->paid_amount / 100, 0, ',', ' ') }} {{ $invoice->company->currency }}</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Solde dû</strong></td>
            <td class="text-right"><strong>{{ number_format($invoice->balance_due / 100, 0, ',', ' ') }} {{ $invoice->company->currency }}</strong></td>
        </tr>
    </table>

    @if ($invoice->company->footer_text)
        <p class="muted" style="margin-top: 24px;">{{ $invoice->company->footer_text }}</p>
    @endif
</body>
</html>
