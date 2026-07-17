<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>État du stock</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>État du stock</h1>
    <p>Généré le {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                @foreach ($warehouses as $warehouse)
                    <th class="text-right">{{ $warehouse->name }}</th>
                @endforeach
                @foreach ($outlets as $outlet)
                    <th class="text-right">{{ $outlet->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['product']->name }}</td>
                    @foreach ($warehouses as $warehouse)
                        @php $level = $row['byLocation']->get('WAREHOUSE:'.$warehouse->id); @endphp
                        <td class="text-right">{{ $level ? number_format(($level->quantity_physical - $level->quantity_reserved) / 100, 0, ',', ' ') : '—' }}</td>
                    @endforeach
                    @foreach ($outlets as $outlet)
                        @php $level = $row['byLocation']->get('OUTLET:'.$outlet->id); @endphp
                        <td class="text-right">{{ $level ? number_format(($level->quantity_physical - $level->quantity_reserved) / 100, 0, ',', ' ') : '—' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
