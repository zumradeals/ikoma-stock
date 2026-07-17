<div>
    @if (! $this->hasDues)
        <div class="flex items-center gap-2 rounded-lg bg-green-50 text-green-700 text-sm px-3 py-2.5">
            <span>✓</span>
            <span>Aucune dette ou commande en attente pour {{ $customer->name }}.</span>
        </div>
    @else
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 space-y-3">
            <p class="text-sm font-semibold text-red-800">Attention : {{ $customer->name }} a des éléments en attente</p>

            @if ($receivables->isNotEmpty())
                <div>
                    <p class="text-xs font-medium text-red-700 mb-1">Créances ouvertes</p>
                    <ul class="space-y-1">
                        @foreach ($receivables as $receivable)
                            <li class="flex justify-between text-sm text-red-800">
                                <span>Échéance {{ $receivable->due_date?->format('d/m/Y') }}</span>
                                <span class="font-medium"><x-money :amount="$receivable->balance_due" /></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($undeliveredInvoices->isNotEmpty())
                <div>
                    <p class="text-xs font-medium text-red-700 mb-1">Commandes non livrées</p>
                    <ul class="space-y-1">
                        @foreach ($undeliveredInvoices as $invoice)
                            <li class="flex justify-between text-sm text-red-800">
                                <span>{{ $invoice->number }}</span>
                                <span class="font-medium"><x-money :amount="$invoice->total_amount" /></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex gap-2 pt-1">
                @if ($this->canBlock)
                    <button type="button" wire:click="block" class="flex-1 rounded-lg bg-red-600 text-white text-sm font-medium py-2">
                        Bloquer
                    </button>
                @endif
                <button type="button" wire:click="continueAnyway" class="flex-1 rounded-lg bg-white border border-red-300 text-red-700 text-sm font-medium py-2">
                    Continuer quand même
                </button>
            </div>
        </div>
    @endif
</div>
