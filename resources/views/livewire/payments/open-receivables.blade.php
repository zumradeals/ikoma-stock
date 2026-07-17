<div class="p-3 space-y-3">
    <h1 class="text-base font-semibold text-ink">Créances ouvertes</h1>

    <input
        type="search"
        wire:model.live.debounce.300ms="search"
        placeholder="Rechercher un client..."
        class="w-full rounded-lg border-gray-200 text-sm"
    >

    @if ($this->receivables->isEmpty())
        <p class="text-center text-ink-soft text-sm py-10">
            {{ $search !== '' ? 'Aucun résultat.' : 'Aucune créance ouverte. 🎉' }}
        </p>
    @else
        <div class="space-y-2">
            @foreach ($this->receivables as $receivable)
                @php
                    $status = $receivable->status;
                    $badgeClass = match ($status) {
                        \App\Enums\ReceivableStatus::OVERDUE  => 'bg-danger-wash text-danger',
                        \App\Enums\ReceivableStatus::PARTIAL  => 'bg-gold-wash text-gold',
                        default                               => 'bg-info-wash text-info',
                    };
                    $badgeIcon = match ($status) {
                        \App\Enums\ReceivableStatus::OVERDUE  => '🔴',
                        \App\Enums\ReceivableStatus::PARTIAL  => '💰',
                        default                               => '⏳',
                    };
                    $sale = $receivable->invoice?->sale;
                @endphp
                @if ($sale)
                    <a
                        href="{{ route('sales.payment', $sale) }}"
                        wire:navigate
                        wire:key="rec-{{ $receivable->id }}"
                        class="flex items-center justify-between rounded-xl border border-line bg-white px-4 py-3 gap-3"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink truncate">
                                {{ $receivable->customer?->name ?? 'Client de passage' }}
                            </p>
                            @if ($receivable->customer?->phone)
                                <p class="text-xs text-ink-soft">{{ $receivable->customer->phone }}</p>
                            @endif
                            @if ($receivable->days_overdue > 0)
                                <p class="text-xs text-danger mt-0.5">{{ $receivable->days_overdue }} jour{{ $receivable->days_overdue > 1 ? 's' : '' }} de retard</p>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold text-ink">
                                <x-money :amount="$receivable->balance_due" />
                            </p>
                            <span class="inline-flex items-center gap-1 rounded-pill px-2 py-0.5 text-[11px] font-extrabold {{ $badgeClass }}">
                                {{ $badgeIcon }} {{ $status->label() }}
                            </span>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
