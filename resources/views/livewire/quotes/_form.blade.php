<div class="space-y-4">

    {{-- Validation errors --}}
    @error('lines') <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-bold text-red-600">{{ $message }}</div> @enderror

    {{-- ── Produits ── --}}
    <div class="rounded-2xl border border-line bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-line flex items-center justify-between">
            <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Articles</p>
        </div>

        {{-- Recherche produit --}}
        <div class="px-4 py-3 border-b border-line relative">
            <input type="search"
                   wire:model.live.debounce.200ms="productSearch"
                   wire:focus="$set('showProductResults', true)"
                   placeholder="Rechercher un produit…"
                   class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">

            @if ($this->productResults->isNotEmpty())
                <div class="absolute left-4 right-4 top-full mt-1 z-20 rounded-xl border border-line bg-white shadow-lg overflow-hidden divide-y divide-line">
                    @foreach ($this->productResults as $product)
                        <button type="button" wire:click="selectProduct({{ $product->id }})"
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-cream transition flex items-center justify-between gap-2">
                            <span class="font-bold text-ink">{{ $product->name }}</span>
                            <span class="text-xs text-ink-soft shrink-0"><x-money :amount="$product->sale_price" /></span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Lignes --}}
        @forelse ($lines as $index => $line)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-line last:border-0" wire:key="line-{{ $index }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-ink">{{ $line['name'] }}</p>
                    <p class="text-xs text-ink-soft"><x-money :amount="$line['unit_price']" /> / {{ $line['unit'] }}</p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" wire:click="updateQuantity({{ $index }}, {{ $line['quantity'] - 1 }})"
                            class="h-7 w-7 rounded-full bg-cream text-ink-soft font-bold text-sm hover:bg-line transition">−</button>
                    <span class="w-6 text-center text-sm font-bold text-ink">{{ $line['quantity'] }}</span>
                    <button type="button" wire:click="updateQuantity({{ $index }}, {{ $line['quantity'] + 1 }})"
                            class="h-7 w-7 rounded-full bg-cream text-ink-soft font-bold text-sm hover:bg-line transition">+</button>
                </div>
                <span class="w-20 text-right text-sm font-extrabold text-ink shrink-0"><x-money :amount="$line['line_total']" /></span>
                <button type="button" wire:click="removeLine({{ $index }})"
                        class="text-line hover:text-danger transition text-sm shrink-0">✕</button>
            </div>
        @empty
            <p class="px-4 py-6 text-sm text-ink-soft text-center">Aucun article ajouté.</p>
        @endforelse

        {{-- Totaux --}}
        @if (count($lines) > 0)
            <div class="px-4 py-3 bg-cream/50 border-t border-line space-y-1">
                @if ($this->discountTotal > 0)
                    <div class="flex justify-between text-xs text-ink-soft">
                        <span>Sous-total</span>
                        <span><x-money :amount="$this->cartTotal" /></span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-danger">
                        <span>Remise</span>
                        <span>− <x-money :amount="$this->discountTotal" /></span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-extrabold text-ink">
                    <span>Total</span>
                    <span><x-money :amount="$this->netTotal" /></span>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Client ── --}}
    <div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-2">
        <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Client (optionnel)</p>

        @if ($this->selectedCustomer)
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-ink">{{ $this->selectedCustomer->name }}</p>
                    <p class="text-xs text-ink-soft">{{ $this->selectedCustomer->phone }}</p>
                </div>
                <button type="button" wire:click="clearCustomer"
                        class="text-xs text-ink-soft hover:text-danger transition">✕</button>
            </div>
        @else
            <div class="relative">
                <input type="search"
                       wire:model.live.debounce.200ms="customerSearch"
                       placeholder="Nom ou téléphone…"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                @if ($this->customerResults->isNotEmpty())
                    <div class="absolute left-0 right-0 top-full mt-1 z-20 rounded-xl border border-line bg-white shadow-lg overflow-hidden divide-y divide-line">
                        @foreach ($this->customerResults as $customer)
                            <button type="button" wire:click="selectCustomer({{ $customer->id }})"
                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-cream transition">
                                <span class="font-bold text-ink">{{ $customer->name }}</span>
                                <span class="text-ink-soft"> · {{ $customer->phone }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ── Options ── --}}
    <div class="rounded-2xl border border-line bg-white px-4 py-3 space-y-3">
        <p class="text-xs font-extrabold text-ink-soft uppercase tracking-widest">Options</p>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Valide jusqu'au</label>
                <input type="date" wire:model.live="validUntil"
                       min="{{ today()->toDateString() }}"
                       class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                @error('validUntil') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft mb-1">Remise</label>
                <div class="flex gap-1.5">
                    <select wire:model.live="discountType"
                            class="rounded-xl border border-line bg-cream px-2 py-2 text-xs text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                        <option value="">Aucune</option>
                        <option value="amount">Montant</option>
                        <option value="percentage">%</option>
                    </select>
                    @if ($discountType)
                        <input type="number" wire:model.live="discountValue"
                               min="0" step="1"
                               placeholder="{{ $discountType === 'percentage' ? '0–100' : 'Montant' }}"
                               class="flex-1 rounded-xl border border-line bg-cream px-2 py-2 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-brand/40">
                    @endif
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-ink-soft mb-1">Notes</label>
            <textarea wire:model="notes" rows="2"
                      class="w-full rounded-xl border border-line bg-cream px-3 py-2 text-sm text-ink resize-none focus:outline-none focus:ring-2 focus:ring-brand/40"
                      placeholder="Conditions, remarques…"></textarea>
        </div>
    </div>

    {{-- ── Action ── --}}
    <button type="button" wire:click="save" wire:loading.attr="disabled"
            class="w-full rounded-xl bg-brand text-white text-sm font-extrabold py-3 hover:brightness-90 active:brightness-75 transition disabled:opacity-60">
        <span wire:loading.remove wire:target="save">Créer le devis</span>
        <span wire:loading wire:target="save">Enregistrement…</span>
    </button>
</div>
