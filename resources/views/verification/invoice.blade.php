<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification de facture — Ikoma</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream flex items-center justify-center px-4 py-12">

<div class="w-full max-w-sm mx-auto">

    {{-- Logo --}}
    <div class="flex items-center justify-center gap-2 mb-8">
        <div class="h-9 w-9 rounded-[10px] bg-brand flex items-center justify-center text-white text-sm font-extrabold shrink-0">
            IK
        </div>
        <span class="text-ink text-lg font-extrabold tracking-tight">Ikoma</span>
    </div>

    @if ($invoice)
        {{-- ── Facture authentique ── --}}
        @php
            $currency = $invoice->company->currency ?? 'FCFA';
            $fmt = fn(int $v) => number_format($v / 100, 0, ',', ' ') . ' ' . $currency;

            $paymentLabel = match ($invoice->payment_status->value) {
                'PAID'         => 'Entièrement payée',
                'PARTIAL'      => 'Partiellement payée',
                'UNPAID'       => 'Non payée',
                'CANCELLED'    => 'Annulée',
                default        => $invoice->payment_status->value,
            };
        @endphp

        <div class="rounded-2xl border-2 border-success/40 bg-white shadow-sm overflow-hidden">
            {{-- Header vert --}}
            <div class="bg-success/10 px-5 py-4 border-b border-success/20 flex items-center gap-3">
                <span class="text-2xl leading-none">✅</span>
                <div>
                    <p class="text-sm font-extrabold text-success">Facture authentique</p>
                    <p class="text-xs text-success/70">Émise par {{ $invoice->company->name }}</p>
                </div>
            </div>

            {{-- Corps --}}
            <div class="px-5 py-4 space-y-3">
                <div class="flex justify-between items-start py-2 border-b border-line text-sm">
                    <span class="text-ink-soft">Numéro</span>
                    <span class="font-bold text-ink">{{ $invoice->number }}</span>
                </div>
                <div class="flex justify-between items-start py-2 border-b border-line text-sm">
                    <span class="text-ink-soft">Date d'émission</span>
                    <span class="font-bold text-ink">{{ $invoice->issue_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-start py-2 border-b border-line text-sm">
                    <span class="text-ink-soft">Montant total</span>
                    <span class="font-extrabold text-ink">{{ $fmt($invoice->total_amount) }}</span>
                </div>
                <div class="flex justify-between items-start py-2 border-b border-line text-sm">
                    <span class="text-ink-soft">Montant payé</span>
                    <span class="font-bold text-success">{{ $fmt($invoice->paid_amount) }}</span>
                </div>
                @if ($invoice->balance_due > 0)
                    <div class="flex justify-between items-start py-2 border-b border-line text-sm">
                        <span class="text-ink-soft">Reste à payer</span>
                        <span class="font-extrabold text-gold">{{ $fmt($invoice->balance_due) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-start py-2 text-sm">
                    <span class="text-ink-soft">Statut</span>
                    <span class="font-bold text-ink">{{ $paymentLabel }}</span>
                </div>
            </div>
        </div>

    @else
        {{-- ── Token invalide ── --}}
        <div class="rounded-2xl border-2 border-danger/40 bg-white shadow-sm overflow-hidden">
            <div class="bg-danger/10 px-5 py-4 border-b border-danger/20 flex items-center gap-3">
                <span class="text-2xl leading-none">❌</span>
                <div>
                    <p class="text-sm font-extrabold text-danger">Facture introuvable</p>
                    <p class="text-xs text-danger/70">Ce lien n'est associé à aucune facture.</p>
                </div>
            </div>
            <div class="px-5 py-4">
                <p class="text-sm text-ink-soft">
                    Le code QR que vous avez scanné ne correspond à aucune facture dans notre système.
                    Il est possible que ce document ait été falsifié ou que le lien soit incorrect.
                </p>
            </div>
        </div>
    @endif

    <p class="text-center text-xs text-ink-soft/60 mt-6">Système de vérification Ikoma</p>

</div>
</body>
</html>
