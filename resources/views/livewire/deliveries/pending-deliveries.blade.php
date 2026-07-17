<div class="p-3 space-y-3">
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach (['all' => 'Toutes', 'today' => "Aujourd'hui", 'overdue' => 'En retard', 'week' => 'Cette semaine'] as $key => $label)
            <button
                type="button"
                wire:click="$set('filter', '{{ $key }}')"
                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium {{ $filter === $key ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-600' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
        @forelse ($this->invoices as $invoice)
            <a href="{{ route('deliveries.show', $invoice) }}" wire:navigate class="flex items-center justify-between px-3 py-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $invoice->number }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $invoice->sale->customer->name ?? 'Client de passage' }} · {{ $invoice->sale->outlet->name }}
                        @if ($invoice->due_date)
                            · Échéance {{ $invoice->due_date->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <x-status-badge :status="$this->status($invoice)" :label="$invoice->delivery_status->label()" />
            </a>
        @empty
            <p class="text-center text-sm text-gray-400 py-10">Aucune livraison en attente.</p>
        @endforelse
    </div>
</div>
