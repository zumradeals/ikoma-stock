<div>

{{-- ════════════════════════════════════════════════════════════════════════
     GABARIT DESKTOP — visible à partir de lg: (1024 px)
     Catalogue + ticket permanent côte à côte, sidebar charcoal
════════════════════════════════════════════════════════════════════════ --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">

    {{-- ── Sidebar charcoal ── --}}
    <aside class="w-20 flex-none bg-charcoal flex flex-col items-center py-5 gap-1.5">
        <div class="mb-5 h-9 w-9 rounded-[11px] bg-brand flex items-center justify-center text-white text-[13px] font-extrabold">
            IK
        </div>

        @php
        $sideItems = [
            ['icon' => '🏠', 'label' => 'Accueil',   'route' => 'app.dashboard', 'active' => false],
            ['icon' => '🛒', 'label' => 'Vendre',     'route' => 'sales.create',  'active' => true],
            ['icon' => '💰', 'label' => 'Paiements',  'route' => 'closing.index', 'active' => false],
            ['icon' => '👥', 'label' => 'Clients',    'route' => 'customers.index','active' => false],
        ];
        @endphp

        @foreach ($sideItems as $item)
            <a href="{{ route($item['route']) }}" wire:navigate
               class="flex w-16 flex-col items-center gap-1 rounded-xl py-2.5 text-[10px] font-extrabold transition
                      {{ $item['active'] ? 'bg-brand/20 text-brand' : 'text-charcoal-line hover:text-white/70' }}">
                <span class="text-lg leading-none">{{ $item['icon'] }}</span>
                {{ $item['label'] }}
            </a>
        @endforeach

        <a href="{{ route('admin.index') }}" wire:navigate
           class="mt-auto flex w-16 flex-col items-center gap-1 rounded-xl py-2.5 text-[10px] font-extrabold text-charcoal-line hover:text-white/70 transition">
            <span class="text-lg leading-none">⚙️</span>
            Gestion
        </a>
    </aside>

    {{-- ── Zone principale ── --}}
    <div class="flex flex-1 flex-col min-w-0">

        {{-- Topbar --}}
        <div class="flex h-14 flex-none items-center gap-3 border-b border-line bg-white px-5">
            <span class="rounded-xl border border-line bg-cream px-3 py-2 text-xs font-extrabold text-ink-soft whitespace-nowrap">
                📍 {{ auth()->user()->outlet?->name ?? 'Boutique' }}
            </span>
            <div class="flex-1">
                {{-- Le catalogue intégré a sa propre barre de recherche --}}
            </div>
            <span class="flex items-center gap-2 text-xs font-extrabold text-ink-soft">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-wash text-xs font-extrabold text-brand">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </span>
                {{ auth()->user()->name }}
            </span>
        </div>

        {{-- Catalogue + ticket --}}
        <div class="flex flex-1 min-h-0">

            {{-- Catalogue produits --}}
            <div class="flex-1 overflow-y-auto">
                @if ($outletId)
                    <livewire:sales.product-catalog :outlet-id="$outletId" wire:key="desktop-catalog-{{ $outletId }}" />
                @else
                    <div class="flex h-full items-center justify-center text-sm text-ink-soft">
                        Aucun point de vente configuré.
                    </div>
                @endif
            </div>

            {{-- Ticket permanent ── --}}
            <aside class="w-80 flex-none border-l border-line bg-white flex flex-col">

                {{-- En-tête ticket : client ── --}}
                <div class="border-b border-line px-4 py-3 space-y-2">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-ink-soft">Client</p>

                    @if ($this->customer)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-extrabold text-ink">{{ $this->customer->name }}</span>
                            <button type="button" wire:click="$set('customerId', null)"
                                    class="text-xs text-ink-soft hover:text-danger transition">✕</button>
                        </div>
                        <livewire:customers.customer-alert :customer="$this->customer" wire:key="dt-alert-{{ $this->customer->id }}" />
                    @elseif ($isPassingCustomer)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-ink-soft">Client de passage</span>
                                <button type="button" wire:click="$set('isPassingCustomer', false)"
                                        class="text-xs text-ink-soft hover:text-danger transition">✕</button>
                            </div>
                            <input type="text" wire:model="passingPhone" placeholder="Téléphone (optionnel)"
                                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                        </div>
                    @else
                        <input type="search" wire:model.live.debounce.300ms="customerSearch"
                               placeholder="Rechercher par nom ou téléphone…"
                               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-xs font-bold text-ink placeholder:font-normal placeholder:text-ink-soft focus:outline-none focus:ring-2 focus:ring-brand/40">

                        @if ($this->customerResults->isNotEmpty())
                            <div class="absolute z-20 left-0 right-0 mt-1 rounded-xl border border-line bg-white shadow-card overflow-hidden divide-y divide-line">
                                @foreach ($this->customerResults as $result)
                                    <button type="button" wire:click="selectCustomer({{ $result->id }})"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-cream transition">
                                        <span class="font-bold text-ink">{{ $result->name }}</span>
                                        <span class="text-ink-soft"> · {{ $result->phone }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <button type="button" wire:click="usePassingCustomer"
                                class="text-xs font-bold text-ink-soft hover:text-brand transition">
                            + Client de passage
                        </button>
                    @endif
                </div>

                {{-- Lignes du panier ── --}}
                <div class="flex-1 overflow-y-auto divide-y divide-line px-1">
                    @forelse ($cart as $productId => $line)
                        <div class="flex items-center gap-2 px-3 py-2.5" wire:key="dt-cart-{{ $productId }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-extrabold text-ink truncate">{{ $line['name'] }}</p>
                                <p class="text-[10px] text-ink-soft"><x-money :amount="$line['unit_price']" /> / {{ $line['unit'] }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button"
                                        wire:click="updateCartQuantity({{ $productId }}, {{ $line['quantity'] - 1 }})"
                                        class="h-6 w-6 rounded-full bg-cream text-ink-soft text-xs font-bold hover:bg-line transition">−</button>
                                <span class="w-5 text-center text-xs font-bold text-ink">{{ $line['quantity'] }}</span>
                                <button type="button"
                                        wire:click="updateCartQuantity({{ $productId }}, {{ $line['quantity'] + 1 }})"
                                        class="h-6 w-6 rounded-full bg-cream text-ink-soft text-xs font-bold hover:bg-line transition">+</button>
                            </div>
                            <span class="w-16 text-right text-xs font-extrabold text-ink">
                                <x-money :amount="$line['unit_price'] * $line['quantity']" />
                            </span>
                            <button type="button" wire:click="removeFromCart({{ $productId }})"
                                    class="text-line hover:text-danger text-xs transition">✕</button>
                        </div>
                    @empty
                        <p class="py-8 text-center text-xs text-ink-soft">Panier vide — cliquez sur un produit</p>
                    @endforelse
                </div>

                {{-- Bas du ticket : remise + paiement + livraison + valider ── --}}
                <div class="border-t border-line px-4 py-3 space-y-3">

                    {{-- Remise (rôles autorisés uniquement) --}}
                    @if ($this->canApplyDiscount && count($cart) > 0)
                        <div class="space-y-1.5">
                            <p class="text-[10px] font-extrabold uppercase tracking-widest text-ink-soft">Prix négocié</p>
                            <div class="flex gap-2">
                                <input type="number" min="0" step="1" wire:model.live="discountAmount"
                                       placeholder="Montant F"
                                       class="flex-1 rounded-xl border border-line bg-cream px-2 py-1.5 text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                                <input type="number" min="0" max="100" step="1" wire:model.live="discountPercentage"
                                       placeholder="ou %"
                                       class="w-16 rounded-xl border border-line bg-cream px-2 py-1.5 text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                            </div>
                        </div>
                    @endif

                    {{-- Totaux --}}
                    <div class="space-y-0.5 text-xs">
                        @if ($this->discountTotal > 0)
                            <div class="flex justify-between text-ink-soft">
                                <span>Sous-total</span>
                                <span><x-money :amount="$this->cartTotal" /></span>
                            </div>
                            <div class="flex justify-between text-danger font-bold">
                                <span>Remise</span>
                                <span>− <x-money :amount="$this->discountTotal" /></span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-extrabold text-ink">
                            <span>Total</span>
                            <span><x-money :amount="$this->netTotal" /></span>
                        </div>
                    </div>

                    {{-- Mode de paiement ── --}}
                    <div class="space-y-1.5">
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-ink-soft">Paiement</p>
                        <div class="flex gap-1.5">
                            @foreach ([
                                ['value' => 'cash_now',   'label' => 'Espèces'],
                                ['value' => 'mobile_now', 'label' => 'Mobile Money'],
                                ['value' => 'later',      'label' => 'Plus tard'],
                            ] as $pm)
                                <button type="button"
                                        wire:click="$set('paymentChoice', '{{ $pm['value'] }}')"
                                        class="flex-1 rounded-xl border py-1.5 text-[11px] font-extrabold transition
                                               {{ $paymentChoice === $pm['value']
                                                  ? 'border-brand bg-brand-wash text-brand'
                                                  : 'border-line bg-cream text-ink-soft hover:border-brand/40' }}">
                                    {{ $pm['label'] }}
                                </button>
                            @endforeach
                        </div>

                        @if ($paymentChoice === 'later')
                            <input type="number" min="0" step="1" wire:model.live="partialAmountInput"
                                   placeholder="Montant reçu maintenant (F)"
                                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                            @if ($this->remainingAmount > 0)
                                <p class="text-xs text-ink-soft">
                                    Reste : <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong>
                                </p>
                            @endif
                        @endif
                    </div>

                    {{-- Livraison ── --}}
                    <div class="space-y-1.5">
                        <p class="text-[10px] font-extrabold uppercase tracking-widest text-ink-soft">Livraison</p>
                        <div class="flex gap-1.5">
                            @foreach ([['value' => 'now', 'label' => '✅ Maintenant'], ['value' => 'later', 'label' => '📦 Plus tard']] as $d)
                                <button type="button"
                                        wire:click="$set('deliveryChoice', '{{ $d['value'] }}')"
                                        class="flex-1 rounded-xl border py-1.5 text-[11px] font-extrabold transition
                                               {{ $deliveryChoice === $d['value']
                                                  ? 'border-brand bg-brand-wash text-brand'
                                                  : 'border-line bg-cream text-ink-soft hover:border-brand/40' }}">
                                    {{ $d['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Avertissements I3 / I4 ── --}}
                    @if ($this->remainingAmount > 0 && ! $this->hasIdentifiedCustomer)
                        <p class="rounded-xl bg-gold-wash px-3 py-2 text-[11px] font-bold text-gold">
                            ⚠️ Reste à payer : ajoute le client pour suivre la dette.
                        </p>
                    @endif
                    @if ($deliveryChoice === 'later' && ! $this->hasIdentifiedCustomer)
                        <p class="rounded-xl bg-gold-wash px-3 py-2 text-[11px] font-bold text-gold">
                            ⚠️ Livraison différée : ajoute le client pour pouvoir le retrouver.
                        </p>
                    @endif

                    @error('form')
                        <p class="rounded-xl bg-danger-wash px-3 py-2 text-[11px] font-bold text-danger">{{ $message }}</p>
                    @enderror

                    {{-- Bouton valider ── --}}
                    @if ($step === 6 && $invoice)
                        <div class="rounded-xl bg-success-wash px-3 py-2 text-xs font-bold text-success text-center">
                            ✓ Vente enregistrée !
                            <a href="{{ route('sales.create') }}" wire:navigate class="underline ml-1">Nouvelle vente</a>
                        </div>
                    @else
                        <button type="button"
                                wire:click="validateSale"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60"
                                @disabled(count($cart) === 0)
                                class="w-full rounded-xl bg-brand py-3 text-sm font-extrabold text-white shadow-brand-glow transition hover:brightness-90 active:brightness-75 disabled:opacity-40 disabled:cursor-not-allowed">
                            <span wire:loading.remove>
                                @if ($this->remainingAmount > 0)
                                    Enregistrer la vente et la dette
                                @else
                                    Confirmer la vente
                                @endif
                            </span>
                            <span wire:loading>Enregistrement…</span>
                        </button>
                    @endif

                </div>
            </aside>

        </div>{{-- /flex body --}}
    </div>{{-- /pos-main --}}
</div>{{-- /desktop --}}


{{-- ════════════════════════════════════════════════════════════════════════
     GABARIT MOBILE — masqué à partir de lg:
     Parcours en 5 écrans-question séquentiels
════════════════════════════════════════════════════════════════════════ --}}
<div class="lg:hidden flex flex-col min-h-screen bg-cream">

    {{-- En-tête des étapes 2-5 --}}
    @if ($step >= 2 && $step <= 5)
        <header class="sticky top-0 z-10 bg-white border-b border-line px-4 pt-3 pb-2">
            <button type="button" wire:click="previousStep"
                    class="mb-2 flex items-center gap-1.5 text-xs font-bold text-ink-soft">
                ‹ Retour
            </button>
            <div class="flex gap-1.5">
                @foreach ([2, 3, 4, 5] as $s)
                    <div class="h-1 flex-1 rounded-full transition-colors {{ $step >= $s ? 'bg-brand' : 'bg-line' }}"></div>
                @endforeach
            </div>
        </header>
    @endif

    {{-- ── Étape 1 : Catalogue ── --}}
    @if ($step === 1)
        @if ($outletId)
            <livewire:sales.product-catalog :outlet-id="$outletId" wire:key="catalog-{{ $outletId }}" />
            <livewire:sales.sale-cart :lines="$cart" wire:key="new-sale-cart" />
        @else
            <div class="flex flex-1 items-center justify-center px-6 py-20 text-center">
                <p class="text-sm text-ink-soft">Aucun point de vente configuré pour votre société.</p>
            </div>
        @endif

        @if ($this->canApplyDiscount && count($cart) > 0)
            <div class="mx-4 mb-4 rounded-2xl border border-line bg-white px-4 py-3 space-y-2">
                <p class="text-[10px] font-extrabold uppercase tracking-widest text-ink-soft">Prix négocié (remise)</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-ink-soft mb-1">Montant fixe (F)</label>
                        <input type="number" min="0" step="1" wire:model.live="discountAmount"
                               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-ink-soft mb-1">Pourcentage (%)</label>
                        <input type="number" min="0" max="100" step="1" wire:model.live="discountPercentage"
                               class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
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

    {{-- ── Étape 2 : Comment paie-t-il ? ── --}}
    @if ($step === 2)
        <div class="flex-1 px-4 py-5 space-y-3">
            <h2 class="text-xl font-extrabold text-ink leading-snug">Le client paie comment ?</h2>
            <p class="text-sm text-ink-soft">Total : <strong class="text-ink"><x-money :amount="$this->netTotal" /></strong></p>

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

            @if ($paymentChoice === 'later')
                <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-2 mt-2">
                    <label class="block text-sm font-extrabold text-ink">Montant reçu maintenant (F)</label>
                    <input type="number" min="0" step="1" wire:model.live="partialAmountInput" placeholder="0"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-3 text-lg font-extrabold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    @if ($this->remainingAmount > 0)
                        <p class="text-sm text-ink-soft">
                            Il restera à payer : <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong>
                        </p>
                    @elseif ($paymentChoice === 'later' && $partialAmountInput !== '')
                        <p class="text-sm text-success font-bold">Tout est couvert ✓</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary wire:click="nextStep"
                                    :disabled="$paymentChoice === ''"
                                    class="{{ $paymentChoice === '' ? 'opacity-40 cursor-not-allowed' : '' }}">
                Suivant
            </x-ikoma.button-primary>
        </div>
    @endif

    {{-- ── Étape 3 : Qui est le client ? ── --}}
    @if ($step === 3)
        <div class="flex-1 px-4 py-5 space-y-3">
            <h2 class="text-xl font-extrabold text-ink leading-snug">Qui est le client ?</h2>

            @if ($this->remainingAmount > 0)
                <p class="text-sm text-ink-soft">
                    Reste à payer : <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong>
                </p>
            @endif

            @error('customer')
                <div class="rounded-xl bg-danger-wash px-3 py-2.5 text-sm font-bold text-danger">{{ $message }}</div>
            @enderror

            <input type="search" wire:model.live.debounce.300ms="customerSearch"
                   placeholder="Rechercher par nom ou téléphone…"
                   class="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm font-bold text-ink placeholder:font-normal placeholder:text-ink-soft focus:outline-none focus:ring-2 focus:ring-brand/40">

            @if ($this->customerResults->isNotEmpty())
                <div class="rounded-2xl border border-line bg-white overflow-hidden divide-y divide-line">
                    @foreach ($this->customerResults as $result)
                        <button type="button" wire:click="selectCustomer({{ $result->id }})"
                                class="w-full text-left px-4 py-3 text-sm hover:bg-cream transition">
                            <span class="font-bold text-ink">{{ $result->name }}</span>
                            <span class="text-ink-soft"> · {{ $result->phone }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            @if ($this->customer)
                <div class="rounded-xl bg-success-wash px-3 py-2.5 text-sm font-bold text-success">
                    ✓ {{ $this->customer->name }}@if ($this->customer->phone) · {{ $this->customer->phone }}@endif
                </div>
                <livewire:customers.customer-alert :customer="$this->customer" wire:key="alert-{{ $this->customer->id }}" />
            @endif

            @if (! $this->customer)
                <button type="button" wire:click="usePassingCustomer"
                        class="w-full rounded-2xl border-2 border-dashed border-line bg-white px-4 py-3 text-sm font-bold text-ink-soft hover:border-brand/40 transition">
                    + Nouveau client (saisir nom / téléphone)
                </button>
            @endif

            @if ($isPassingCustomer && ! $this->customer)
                <div class="rounded-2xl border border-line bg-white px-4 py-4">
                    <input type="text" wire:model="passingPhone" placeholder="Téléphone (pour suivre la dette)"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-3 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                </div>
            @endif
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary wire:click="nextStep">Suivant</x-ikoma.button-primary>
        </div>
    @endif

    {{-- ── Étape 4 : Livraison ── --}}
    @if ($step === 4)
        <div class="flex-1 px-4 py-5 space-y-3">
            <h2 class="text-xl font-extrabold text-ink leading-snug">
                Il repart avec la marchandise aujourd'hui ?
            </h2>

            @error('delivery')
                <div class="rounded-xl bg-danger-wash px-3 py-2.5 text-sm font-bold text-danger">{{ $message }}</div>
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

            @if ($deliveryChoice === 'later' && ! $this->hasIdentifiedCustomer)
                <div class="rounded-2xl border border-line bg-white px-4 py-4 space-y-3">
                    <p class="text-sm font-bold text-ink">
                        Pour retrouver le client lors de la livraison, ajoute son numéro.
                    </p>
                    <input type="text" wire:model="passingPhone" placeholder="Téléphone du client"
                           class="w-full rounded-xl border border-line bg-cream px-3 py-3 text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                </div>
            @endif
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary wire:click="nextStep">Suivant</x-ikoma.button-primary>
        </div>
    @endif

    {{-- ── Étape 5 : Confirmation ── --}}
    @if ($step === 5)
        <div class="flex-1 px-4 py-5 space-y-4">
            <h2 class="text-xl font-extrabold text-ink leading-snug">Confirmer la vente</h2>

            @error('form')
                <div class="rounded-xl bg-danger-wash px-3 py-2.5 text-sm font-bold text-danger">{{ $message }}</div>
            @enderror

            <div class="rounded-2xl bg-white border border-line px-4 py-4 space-y-3 text-sm">
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

                <div class="space-y-0.5">
                    <p class="text-ink">Il paie <strong><x-money :amount="$this->paidAmount" /></strong> maintenant</p>
                    @if ($this->remainingAmount > 0)
                        <p class="text-ink-soft">Il restera à payer : <strong class="text-ink"><x-money :amount="$this->remainingAmount" /></strong></p>
                    @else
                        <p class="text-success font-bold">Tout est réglé ✓</p>
                    @endif
                </div>

                <hr class="border-line">

                <p class="text-ink">Client : <strong>
                    @if ($this->customer)
                        {{ $this->customer->name }}
                    @elseif ($isPassingCustomer && $passingPhone)
                        Nouveau client · {{ $passingPhone }}
                    @else
                        Client de passage
                    @endif
                </strong></p>

                <p class="text-ink">Livraison : <strong>{{ $deliveryChoice === 'now' ? 'Il emporte la marchandise maintenant' : 'À livrer plus tard' }}</strong></p>
            </div>
        </div>

        <div class="px-4 pb-6 pt-2">
            <x-ikoma.button-primary wire:click="validateSale"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-60">
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

    {{-- ── Étape 6 : Facture ── --}}
    @if ($step === 6 && $invoice)
        <div class="flex-1 px-4 py-5 space-y-4">
            <div class="rounded-xl bg-success-wash px-4 py-3 text-sm font-bold text-success">
                ✓ Vente enregistrée avec succès.
            </div>
            <livewire:components.invoice-pdf-viewer :invoice="$invoice" wire:key="new-sale-viewer-{{ $invoice->id }}" />
            <a href="{{ route('sales.create') }}" wire:navigate
               class="block text-center py-3 text-sm font-extrabold text-brand">
                Nouvelle vente →
            </a>
        </div>
    @endif

</div>{{-- /mobile --}}

</div>{{-- root --}}
