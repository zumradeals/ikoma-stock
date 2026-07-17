<div>
    @if ($step <= 5)
        <div class="flex items-center gap-1 px-3 py-2 bg-white border-b border-gray-100">
            @foreach (['Catalogue', 'Client', 'Paiement', 'Livraison', 'Récap'] as $i => $label)
                <div class="flex-1 h-1 rounded-full {{ $step >= $i + 1 ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            @endforeach
        </div>
    @endif

    {{-- Étape 1 : Catalogue --}}
    @if ($step === 1)
        @if ($outletId)
            <livewire:sales.product-catalog :outlet-id="$outletId" wire:key="catalog-{{ $outletId }}" />
            <livewire:sales.sale-cart :lines="$cart" wire:key="new-sale-cart" />
        @else
            <p class="text-center text-sm text-gray-400 py-10 px-4">Aucun point de vente configuré pour votre société.</p>
        @endif
    @endif

    {{-- Étape 2 : Client --}}
    @if ($step === 2)
        <div class="p-4 space-y-4">
            <input
                type="search"
                wire:model.live.debounce.300ms="customerSearch"
                placeholder="Rechercher par nom ou téléphone..."
                class="w-full rounded-lg border-gray-200 text-sm"
            >

            @if ($this->customerResults->isNotEmpty())
                <div class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
                    @foreach ($this->customerResults as $result)
                        <button
                            type="button"
                            wire:click="selectCustomer({{ $result->id }})"
                            class="w-full text-left px-3 py-2.5 text-sm"
                        >
                            <span class="font-medium text-gray-900">{{ $result->name }}</span>
                            <span class="text-gray-400"> — {{ $result->phone }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            <button
                type="button"
                wire:click="usePassingCustomer"
                class="w-full rounded-lg border border-dashed border-gray-300 text-gray-600 text-sm font-medium py-2.5"
            >
                Client de passage
            </button>

            @if ($isPassingCustomer)
                <input
                    type="text"
                    wire:model="passingPhone"
                    placeholder="Téléphone (optionnel)"
                    class="w-full rounded-lg border-gray-200 text-sm"
                >
            @endif

            @if ($this->customer)
                <div class="rounded-lg bg-indigo-50 px-3 py-2 text-sm text-indigo-800">
                    Client sélectionné : <strong>{{ $this->customer->name }}</strong>
                </div>
                <livewire:customers.customer-alert :customer="$this->customer" wire:key="alert-{{ $this->customer->id }}" />
            @endif
        </div>
    @endif

    {{-- Étape 3 : Paiement --}}
    @if ($step === 3)
        <div class="p-4 space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 grid grid-cols-3 gap-2 text-center">
                <div>
                    <p class="text-xs text-gray-400">Total</p>
                    <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->cartTotal" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Payé</p>
                    <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->paidAmount" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Reste</p>
                    <p class="text-sm font-semibold {{ $this->remainingAmount > 0 ? 'text-red-600' : 'text-green-600' }}"><x-money :amount="$this->remainingAmount" /></p>
                </div>
            </div>

            @if ($this->canApplyDiscount)
                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Remise (facultatif)</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label value="Montant fixe" />
                            <x-text-input wire:model.live="discountAmount" type="number" step="1" min="0" class="block mt-1 w-full" />
                        </div>
                        <div>
                            <x-input-label value="ou pourcentage %" />
                            <x-text-input wire:model.live="discountPercentage" type="number" step="1" min="0" max="100" class="block mt-1 w-full" />
                        </div>
                    </div>
                    @if ($this->discountTotal > 0)
                        <p class="text-xs text-gray-500">Remise appliquée : <x-money :amount="$this->discountTotal" /></p>
                    @endif
                </div>
            @endif

            @if ($this->remainingAmount > 0 && ! $this->customer)
                <p class="text-xs text-orange-600 bg-orange-50 rounded-lg px-3 py-2">
                    Aucune créance ne sera enregistrée pour un client de passage : le reste ne sera pas suivi.
                </p>
            @endif

            @foreach ($paymentLines as $index => $line)
                <div class="flex items-center gap-2" wire:key="payment-line-{{ $index }}">
                    <select wire:model="paymentLines.{{ $index }}.method" class="flex-1 rounded-lg border-gray-200 text-sm">
                        @foreach (\App\Enums\PaymentMethod::cases() as $m)
                            @if ($m !== \App\Enums\PaymentMethod::CUSTOMER_CREDIT)
                                <option value="{{ $m->value }}">{{ $m->value }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="number" min="0" wire:model="paymentLines.{{ $index }}.amount" class="w-28 rounded-lg border-gray-200 text-sm" placeholder="Montant">
                    <button type="button" wire:click="removePaymentLine({{ $index }})" class="text-gray-300">✕</button>
                </div>
            @endforeach

            <button type="button" wire:click="addPaymentLine" class="w-full rounded-lg border border-dashed border-gray-300 text-gray-600 text-sm font-medium py-2">
                + Ajouter un mode de paiement
            </button>
        </div>
    @endif

    {{-- Étape 4 : Livraison --}}
    @if ($step === 4)
        <div class="p-4 space-y-3">
            <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4">
                <input type="radio" wire:model="deliveryChoice" value="now">
                <span class="text-sm font-medium text-gray-900">Livrer maintenant</span>
            </label>
            <label class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4">
                <input type="radio" wire:model="deliveryChoice" value="later">
                <span class="text-sm font-medium text-gray-900">Livrer plus tard</span>
            </label>
        </div>
    @endif

    {{-- Étape 5 : Récapitulatif --}}
    @if ($step === 5)
        <div class="p-4 space-y-4">
            @error('form') <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $message }}</p> @enderror

            <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
                <p class="text-sm font-semibold text-gray-900">Récapitulatif</p>
                @foreach ($cart as $line)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $line['name'] }} × {{ $line['quantity'] }}</span>
                        <span class="text-gray-900"><x-money :amount="$line['unit_price'] * $line['quantity']" /></span>
                    </div>
                @endforeach
                @if ($this->discountTotal > 0)
                    <div class="flex justify-between text-sm text-red-600">
                        <span>Remise</span>
                        <span>- <x-money :amount="$this->discountTotal" /></span>
                    </div>
                @endif
                <div class="border-t border-gray-100 pt-2 flex justify-between text-sm font-semibold">
                    <span>Total</span>
                    <span><x-money :amount="$this->cartTotal - $this->discountTotal" /></span>
                </div>
            </div>

            <div class="text-sm text-gray-600 space-y-1">
                <p>Client : <strong>{{ $this->customer->name ?? 'Client de passage' }}</strong></p>
                <p>Paiement : <strong><x-money :amount="$this->paidAmount" /></strong> sur <strong><x-money :amount="$this->cartTotal - $this->discountTotal" /></strong></p>
                <p>Livraison : <strong>{{ $deliveryChoice === 'now' ? 'Maintenant' : 'Plus tard' }}</strong></p>
            </div>

            <button type="button" wire:click="validateSale" class="w-full rounded-lg bg-indigo-600 text-white text-sm font-medium py-3">
                Valider la vente
            </button>
        </div>
    @endif

    {{-- Étape 6 : Facture --}}
    @if ($step === 6 && $invoice)
        <div class="p-4">
            <div class="rounded-lg bg-green-50 text-green-700 text-sm px-3 py-2.5 mb-3">Vente validée avec succès.</div>
            <livewire:components.invoice-pdf-viewer :invoice="$invoice" wire:key="new-sale-viewer-{{ $invoice->id }}" />
            <a href="{{ route('sales.create') }}" wire:navigate class="block text-center mt-4 text-sm text-indigo-600 font-medium">
                Nouvelle vente
            </a>
        </div>
    @endif

    @if ($step >= 2 && $step <= 5)
        <div class="flex gap-2 px-3 pb-4">
            <button type="button" wire:click="previousStep" class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5">
                Précédent
            </button>
            @if ($step < 5)
                <button type="button" wire:click="nextStep" class="flex-1 rounded-lg bg-indigo-600 text-white text-sm font-medium py-2.5">
                    Suivant
                </button>
            @endif
        </div>
    @endif
</div>
