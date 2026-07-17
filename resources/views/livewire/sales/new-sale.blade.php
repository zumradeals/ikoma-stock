<div class="flex flex-col min-h-screen bg-cream">

    {{-- ── En-tête des étapes 2-5 ── --}}
    @if ($step >= 2 && $step <= 5)
        <header class="sticky top-0 z-10 bg-white border-b border-line px-4 pt-3 pb-2">
            {{-- Bouton retour --}}
            <button
                type="button"
                wire:click="previousStep"
                class="mb-2 flex items-center gap-1.5 text-xs font-bold text-ink-soft"
            >
                ‹ Retour
            </button>

            {{-- Barre de progression (4 points = étapes 2 à 5) --}}
            <div class="flex gap-1.5">
                @foreach ([2, 3, 4, 5] as $s)
                    <div class="h-1 flex-1 rounded-full transition-colors {{ $step >= $s ? 'bg-brand' : 'bg-line' }}"></div>
                @endforeach
            </div>
        </header>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         Étape 1 — "Que vend-on ?"
    ════════════════════════════════════════════════════════════════ --}}
    @if ($step === 1)
        @if ($outletId)
            <livewire:sales.product-catalog :outlet-id="$outletId" wire:key="catalog-{{ $outletId }}" />
            <livewire:sales.sale-cart :lines="$cart" wire:key="new-sale-cart" />
        @else
            <div class="flex flex-1 items-center justify-center px-6 py-20 text-center">
                <p class="text-sm text-ink-soft">Aucun point de vente configuré pour votre société.</p>
            </div>
        @endif

        {{-- Remise (rôles autorisés uniquement — absent du DOM pour SALESPERSON) --}}
        @if ($this->canApplyDiscount && count($cart) > 0)
            <div class="mx-4 mb-4 rounded-2xl border border-line bg-white px-4 py-3 space-y-2">
                <p class="text-[10px] font-extrabold uppercase tracking-widest text-ink-soft">Prix négocié (remise)</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-ink-soft mb-1">Montant fixe (F)</label>
                        <input
                            type="number"
                            min="0"
                            step="1"
                            wire:model.live="discountAmount"
                            class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40"
                        >
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-ink-soft mb-1">Pourcentage (%)</label>
                        <input
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            wire:model.live="discountPercentage"
                            class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40"
                        >
                    </div>
                </div>
                @if ($this->discountTotal > 0)
                    <p class="text-xs text-ink-soft">
                        Remise : <strong class="text-ink"><x-money :amount="$this->discountTotal" /></strong>
                        → Net : <strong class="text-ink"><x-money :amount="$this->netTotal" /></strong>
                    </p>
                @endif
            </div>
        @endif
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         Étape 2 — "Le client paie comment ?"
    ════════════════════════════════════════════════════════════════ --}}
    @if ($step === 2)
        <div class="flex-1 px-4 py-5 space-y-3">
            <h2 class="text-xl font-extrabold text-ink leading-snug">Le client paie comment ?</h2>

            {{-- Total à payer --}}
            <p class="text-sm text-ink-soft">
                Total : <strong class="text-ink"><x-money :amount="$this->netTotal" /></strong>
            </p>

            <div class="space-y-2 pt-1">
                <div wire:click="$set('paymentChoice', 'cash_now')">
                    <x-ikoma.option-card :selected="$paymentChoice === 'cash_now'" icon="💵">
                        Tout maintenant, en espèces
                    </x-ikoma.option-card>
                </div>

                <div wire:click="$set('paymentChoice', 'mobile_now')">
                    <x-ikoma.option-card :selected="$paymentChoice === 'mobile_now'" icon="📱">
                        Tout maintenant, Mobile Money
                    </x-ikoma.option-card>
                </div>

                <div wire:click="$set('paymentChoice', 'later')">
                    <x-ikoma.option-card :selected="$paymentChoice === 'later'" icon="🤝">
                        Il paiera plus tard (une partie ou tout)
                    </x-ikoma.option-card>
                </div>
            </div>

            {{-- Champ montant reçu — uniquement si "plus tard" --}}
            @if ($paymentChoice === 'later')
                <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-2 mt-2">
                    <label class="block text-sm font-extrabold text-ink">
                        Montant reçu maintenant (F)
                    </label>
                    <input
                        type="number"
                        min="0"
                        step="1"
                        wire:model.live="partialAmountInput"
                        placeholder="0"
                        class="w-full rounded-xl border border-line bg-cream px-3 py-3 text-lg font-extrabold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40"
                    >
                    @if ($this->remainingAmount > 0)
                        <p class="text-sm text-ink-soft">
                            Il restera à payer :
                            <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong>
                        </p>
                    @elseif ($paymentChoice === 'later' && $partialAmountInput !== '')
                        <p class="text-sm text-success font-bold">Tout est couvert ✓</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary
                wire:click="nextStep"
                :disabled="$paymentChoice === ''"
                class="{{ $paymentChoice === '' ? 'opacity-40 cursor-not-allowed' : '' }}"
            >
                Suivant
            </x-ikoma.button-primary>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         Étape 3 — "Qui est le client ?"
    ════════════════════════════════════════════════════════════════ --}}
    @if ($step === 3)
        <div class="flex-1 px-4 py-5 space-y-3">
            <h2 class="text-xl font-extrabold text-ink leading-snug">Qui est le client ?</h2>

            @if ($this->remainingAmount > 0)
                <p class="text-sm text-ink-soft">
                    Reste à payer : <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong>
                </p>
            @endif

            @error('customer')
                <div class="rounded-xl bg-danger-wash px-3 py-2.5 text-sm font-bold text-danger">
                    {{ $message }}
                </div>
            @enderror

            {{-- Recherche client existant --}}
            <input
                type="search"
                wire:model.live.debounce.300ms="customerSearch"
                placeholder="Rechercher par nom ou téléphone…"
                class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm font-bold text-ink placeholder:font-normal placeholder:text-ink-soft focus:outline-none focus:ring-2 focus:ring-brand/40"
            >

            @if ($this->customerResults->isNotEmpty())
                <div class="rounded-2xl border border-line bg-white overflow-hidden divide-y divide-line">
                    @foreach ($this->customerResults as $result)
                        <button
                            type="button"
                            wire:click="selectCustomer({{ $result->id }})"
                            class="w-full text-left px-4 py-3 text-sm hover:bg-cream transition"
                        >
                            <span class="font-bold text-ink">{{ $result->name }}</span>
                            <span class="text-ink-soft"> · {{ $result->phone }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Client sélectionné --}}
            @if ($this->customer)
                <div class="rounded-xl bg-success-wash px-3 py-2.5 text-sm font-bold text-success">
                    ✓ {{ $this->customer->name }}
                    @if ($this->customer->phone)
                        · {{ $this->customer->phone }}
                    @endif
                </div>
                <livewire:customers.customer-alert :customer="$this->customer" wire:key="alert-{{ $this->customer->id }}" />
            @endif

            {{-- Nouveau client de passage --}}
            @if (! $this->customer)
                <button
                    type="button"
                    wire:click="usePassingCustomer"
                    class="w-full rounded-2xl border-2 border-dashed border-line bg-white px-4 py-3 text-sm font-bold text-ink-soft hover:border-brand/40 transition"
                >
                    + Nouveau client (saisir nom / téléphone)
                </button>
            @endif

            @if ($isPassingCustomer && ! $this->customer)
                <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-3">
                    <input
                        type="text"
                        wire:model="passingPhone"
                        placeholder="Téléphone (pour suivre la dette)"
                        class="w-full rounded-xl border border-line bg-cream px-3 py-3 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40"
                    >
                </div>
            @endif
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary wire:click="nextStep">
                Suivant
            </x-ikoma.button-primary>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         Étape 4 — "Il repart avec la marchandise aujourd'hui ?"
    ════════════════════════════════════════════════════════════════ --}}
    @if ($step === 4)
        <div class="flex-1 px-4 py-5 space-y-3">
            <h2 class="text-xl font-extrabold text-ink leading-snug">
                Il repart avec la marchandise aujourd'hui ?
            </h2>

            @error('delivery')
                <div class="rounded-xl bg-danger-wash px-3 py-2.5 text-sm font-bold text-danger">
                    {{ $message }}
                </div>
            @enderror

            <div class="space-y-2 pt-1">
                <div wire:click="$set('deliveryChoice', 'now')">
                    <x-ikoma.option-card :selected="$deliveryChoice === 'now'" icon="✅">
                        Oui, il l'emporte maintenant
                    </x-ikoma.option-card>
                </div>

                <div wire:click="$set('deliveryChoice', 'later')">
                    <x-ikoma.option-card :selected="$deliveryChoice === 'later'" icon="📦">
                        Non, à livrer plus tard
                    </x-ikoma.option-card>
                </div>
            </div>

            {{-- I4 : si livraison différée et pas encore de client → collecter le numéro ici --}}
            @if ($deliveryChoice === 'later' && ! $this->hasIdentifiedCustomer())
                <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-3">
                    <p class="text-sm font-bold text-ink">
                        Pour retrouver le client lors de la livraison, ajoute son numéro.
                    </p>
                    <input
                        type="text"
                        wire:model="passingPhone"
                        placeholder="Téléphone du client"
                        class="w-full rounded-xl border border-line bg-cream px-3 py-3 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40"
                    >
                </div>
            @endif
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary wire:click="nextStep">
                Suivant
            </x-ikoma.button-primary>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         Étape 5 — Confirmation
    ════════════════════════════════════════════════════════════════ --}}
    @if ($step === 5)
        <div class="flex-1 px-4 py-5 space-y-4">
            <h2 class="text-xl font-extrabold text-ink leading-snug">Confirmer la vente</h2>

            @error('form')
                <div class="rounded-xl bg-danger-wash px-3 py-2.5 text-sm font-bold text-danger">
                    {{ $message }}
                </div>
            @enderror

            <div class="rounded-2xl bg-white border border-line px-4 py-4 space-y-3 text-sm">
                {{-- Articles --}}
                <div class="space-y-1">
                    @foreach ($cart as $line)
                        <div class="flex justify-between">
                            <span class="text-ink-soft">{{ $line['name'] }} × {{ $line['quantity'] }}</span>
                            <span class="font-bold text-ink"><x-money :amount="$line['unit_price'] * $line['quantity']" /></span>
                        </div>
                    @endforeach

                    @if ($this->discountTotal > 0)
                        <div class="flex justify-between text-danger">
                            <span>Remise</span>
                            <span class="font-bold">− <x-money :amount="$this->discountTotal" /></span>
                        </div>
                    @endif

                    <div class="border-t border-line pt-2 flex justify-between font-extrabold text-ink">
                        <span>Total</span>
                        <span><x-money :amount="$this->netTotal" /></span>
                    </div>
                </div>

                <hr class="border-line">

                {{-- Paiement --}}
                <div class="space-y-0.5">
                    <p class="text-ink">
                        Il paie <strong><x-money :amount="$this->paidAmount" /></strong> maintenant
                    </p>
                    @if ($this->remainingAmount > 0)
                        <p class="text-ink-soft">
                            Il restera à payer : <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong>
                        </p>
                    @else
                        <p class="text-success font-bold">Tout est réglé ✓</p>
                    @endif
                </div>

                <hr class="border-line">

                {{-- Client --}}
                <p class="text-ink">
                    Client :
                    <strong>
                        @if ($this->customer)
                            {{ $this->customer->name }}
                        @elseif ($isPassingCustomer && $passingPhone)
                            Nouveau client · {{ $passingPhone }}
                        @else
                            Client de passage
                        @endif
                    </strong>
                </p>

                {{-- Livraison --}}
                <p class="text-ink">
                    Livraison :
                    <strong>{{ $deliveryChoice === 'now' ? 'Il emporte la marchandise maintenant' : 'À livrer plus tard' }}</strong>
                </p>
            </div>
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary
                wire:click="validateSale"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60"
            >
                <span wire:loading.remove>
                    @if ($this->remainingAmount > 0)
                        Enregistrer la vente et la dette
                    @else
                        Confirmer la vente
                    @endif
                </span>
                <span wire:loading>Enregistrement…</span>
            </x-ikoma.button-primary>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════
         Étape 6 — Facture
    ════════════════════════════════════════════════════════════════ --}}
    @if ($step === 6 && $invoice)
        <div class="flex-1 px-4 py-5 space-y-4">
            <div class="rounded-xl bg-success-wash px-4 py-3 text-sm font-bold text-success">
                ✓ Vente enregistrée avec succès.
            </div>
            <livewire:components.invoice-pdf-viewer :invoice="$invoice" wire:key="new-sale-viewer-{{ $invoice->id }}" />
            <a
                href="{{ route('sales.create') }}"
                wire:navigate
                class="block text-center py-3 text-sm font-extrabold text-brand"
            >
                Nouvelle vente →
            </a>
        </div>
    @endif

</div>
