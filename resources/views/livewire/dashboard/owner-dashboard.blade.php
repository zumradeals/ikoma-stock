<div class="p-3 space-y-4">
    <div class="grid grid-cols-2 gap-2">
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">💰 Ventes aujourd'hui</p>
            <p class="text-lg font-semibold text-gray-900"><x-money :amount="$this->todaySales['total']" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">✅ Encaissé aujourd'hui</p>
            <p class="text-lg font-semibold text-gray-900"><x-money :amount="$this->cashCollected" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">⚠️ Restant dû clients</p>
            <p class="text-lg font-semibold text-red-600"><x-money :amount="$this->outstandingReceivables" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">📦 Vendu non livré</p>
            <p class="text-lg font-semibold text-gray-900">{{ $this->unpaidDeliveries->count() }}</p>
        </div>
    </div>

    @php $alertCount = $this->lowStockAlerts->count() + $this->overdueDeliveries->count(); @endphp
    <div class="rounded-xl border border-red-200 bg-red-50 p-3">
        <p class="text-xs text-red-700">🚨 Alertes</p>
        <p class="text-lg font-semibold text-red-800">{{ $alertCount }}</p>
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Ventes du jour par point de vente</h2>
        <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
            @forelse ($this->todaySales['by_outlet'] as $outletId => $amount)
                <div class="flex justify-between px-3 py-2 text-sm">
                    <span class="text-gray-600">{{ $this->outletNames[$outletId] ?? '—' }}</span>
                    <span class="font-medium text-gray-900"><x-money :amount="$amount" /></span>
                </div>
            @empty
                <p class="text-center text-sm text-gray-400 py-6">Aucune vente aujourd'hui.</p>
            @endforelse
        </div>
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Ventes du jour par vendeur</h2>
        <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
            @forelse ($this->topSellersToday as $row)
                <div class="flex justify-between px-3 py-2 text-sm">
                    <span class="text-gray-600">{{ $this->userNames[$row['user_id']] ?? '—' }}</span>
                    <span class="font-medium text-gray-900"><x-money :amount="$row['total']" /></span>
                </div>
            @empty
                <p class="text-center text-sm text-gray-400 py-6">Aucune vente aujourd'hui.</p>
            @endforelse
        </div>
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Transferts en attente</h2>
        <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
            @forelse ($this->transfersInTransit as $transfer)
                <div class="flex justify-between px-3 py-2 text-sm">
                    <span class="text-gray-600">{{ $transfer->number }}</span>
                    <x-status-badge status="orange" :label="$transfer->status->label()" />
                </div>
            @empty
                <p class="text-center text-sm text-gray-400 py-6">Aucun transfert en cours.</p>
            @endforelse
        </div>
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Commandes en retard</h2>
        <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
            @forelse ($this->overdueDeliveries as $invoice)
                <a href="{{ route('deliveries.show', $invoice) }}" wire:navigate class="flex justify-between px-3 py-2 text-sm">
                    <span class="text-gray-600">{{ $invoice->number }}</span>
                    <x-status-badge status="red" :label="$invoice->delivery_status->label()" />
                </a>
            @empty
                <p class="text-center text-sm text-gray-400 py-6">Aucun retard.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-3">
        <p class="text-xs text-gray-400">État du stock (valeur estimée)</p>
        <p class="text-lg font-semibold text-gray-900"><x-money :amount="$this->stockValue" /></p>
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Alertes</h2>
        <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
            @foreach ($this->lowStockAlerts as $product)
                <a href="{{ route('stock.index') }}" wire:navigate class="flex justify-between px-3 py-2 text-sm">
                    <span class="text-gray-600">Stock faible : {{ $product->name }}</span>
                    <x-status-badge status="red" label="Stock faible" />
                </a>
            @endforeach
            @foreach ($this->overdueDeliveries as $invoice)
                <a href="{{ route('deliveries.show', $invoice) }}" wire:navigate class="flex justify-between px-3 py-2 text-sm">
                    <span class="text-gray-600">Retard livraison : {{ $invoice->number }}</span>
                    <x-status-badge status="red" label="Retard" />
                </a>
            @endforeach
            @if ($alertCount === 0)
                <p class="text-center text-sm text-gray-400 py-6">Aucune alerte.</p>
            @endif
        </div>
    </div>
</div>
