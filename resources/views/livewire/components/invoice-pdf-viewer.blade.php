<div class="p-4 space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400">Facture</p>
                <p class="text-base font-semibold text-gray-900">{{ $invoice->number }}</p>
            </div>
            <x-status-badge
                :status="match($invoice->payment_status->value) {
                    'PAID' => 'green',
                    'PARTIAL' => 'orange',
                    'OVERDUE' => 'red',
                    'CANCELLED' => 'gray',
                    default => 'gray',
                }"
                :label="$invoice->payment_status->label()"
            />
        </div>

        <div class="divide-y divide-gray-100">
            @foreach ($invoice->sale->saleLines as $line)
                <div class="flex justify-between py-1.5 text-sm">
                    <span class="text-gray-600">{{ $line->product->name }} × {{ $line->quantity }}</span>
                    <span class="text-gray-900 font-medium"><x-money :amount="$line->line_total" /></span>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 pt-2 space-y-1">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total</span>
                <span class="font-semibold text-gray-900"><x-money :amount="$invoice->total_amount" /></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Payé</span>
                <span class="text-gray-700"><x-money :amount="$invoice->paid_amount" /></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Reste dû</span>
                <span class="font-semibold text-red-600"><x-money :amount="$invoice->balance_due" /></span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <a
            href="{{ route('invoices.download', $invoice) }}"
            class="rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5 text-center"
        >
            Télécharger
        </a>

        @if ($this->whatsappUrl)
            <a
                href="{{ $this->whatsappUrl }}"
                target="_blank"
                rel="noopener"
                class="rounded-lg bg-green-600 text-white text-sm font-medium py-2.5 text-center"
            >
                WhatsApp
            </a>
        @else
            <span class="rounded-lg bg-gray-100 text-gray-400 text-sm font-medium py-2.5 text-center" title="Aucun téléphone client">
                WhatsApp
            </span>
        @endif

        <a
            href="{{ route('invoices.download', $invoice) }}"
            target="_blank"
            rel="noopener"
            class="rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5 text-center"
        >
            Imprimer
        </a>
    </div>
</div>
