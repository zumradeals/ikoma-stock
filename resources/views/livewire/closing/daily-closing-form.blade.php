<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="closing" />
    <div class="flex-1 overflow-y-auto">
<div class="p-3 space-y-4">
    @if ($noOutlet)
        <p class="text-center text-sm text-gray-400 py-10 px-4">
            Aucun point de vente configuré pour votre société.
            @if (auth()->user()->role === \App\Enums\UserRole::ADMIN_COMPANY)
                <a href="{{ route('admin.index') }}" wire:navigate class="text-orange-600 font-medium">Créer un point de vente</a>
            @endif
        </p>
    @else
    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">Point de journée — {{ $closing->business_date }}</h1>
        <x-status-badge
            :status="match($closing->status->value) { 'VALIDATED' => 'green', 'PENDING_VALIDATION' => 'orange', 'REJECTED' => 'red', default => 'gray' }"
            :label="$closing->status->label()"
        />
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
        <p class="text-sm font-semibold text-gray-900">Ventes par mode de paiement</p>
        @foreach (['cash' => 'Espèces', 'mobile_money' => 'Mobile Money', 'bank_transfer' => 'Virement', 'check' => 'Chèque', 'customer_credit' => 'Crédit client', 'other' => 'Autre'] as $key => $label)
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">{{ $label }}</span>
                <span class="text-gray-900"><x-money :amount="$this->summary[$key]" /></span>
            </div>
        @endforeach
        <div class="border-t border-gray-100 pt-2 flex justify-between text-sm font-semibold">
            <span>Total encaissé</span>
            <span><x-money :amount="$this->summary['total']" /></span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Anciennes créances encaissées</p>
            <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->oldReceivablesCollected" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Remises accordées</p>
            <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->discounts" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Factures annulées</p>
            <p class="text-sm font-semibold text-gray-900">{{ $this->cancelledInvoices }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Produits livrés</p>
            <p class="text-sm font-semibold text-gray-900">{{ number_format($this->deliveredProducts / 100, 0, ',', ' ') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3 col-span-2">
            <p class="text-xs text-gray-400">Commandes non livrées</p>
            <p class="text-sm font-semibold text-gray-900">{{ $this->undeliveredOrders }}</p>
        </div>
    </div>

    @if (in_array($closing->status, [\App\Enums\DailyClosingStatus::OPEN, \App\Enums\DailyClosingStatus::REJECTED], true))
        <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-700">Montant physique en caisse</label>
                <input type="number" min="0" wire:model.live="declaredCash" class="mt-1 block w-full rounded-lg border-gray-200">
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Différence</span>
                <span class="font-semibold {{ $this->difference !== 0 ? 'text-red-600' : 'text-green-600' }}">
                    <x-money :amount="$this->difference" />
                </span>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Observation</label>
                <textarea wire:model="observations" rows="2" class="mt-1 block w-full rounded-lg border-gray-200"></textarea>
            </div>

            <button type="button" wire:click="close" class="w-full rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5">
                Fermer la journée
            </button>
        </div>
    @endif

    @if ($this->canValidate && $closing->status === \App\Enums\DailyClosingStatus::PENDING_VALIDATION)
        <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <p class="text-sm font-semibold text-gray-900">Validation responsable</p>
            <div class="flex gap-2">
                <button type="button" wire:click="requestValidate" class="flex-1 rounded-lg bg-green-600 text-white text-sm font-medium py-2.5">
                    Valider
                </button>
            </div>
            <div>
                <textarea wire:model="rejectReason" rows="2" placeholder="Motif de rejet" class="block w-full rounded-lg border-gray-200"></textarea>
                @error('rejectReason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <button type="button" wire:click="reject" class="mt-2 w-full rounded-lg bg-red-600 text-white text-sm font-medium py-2.5">
                    Rejeter
                </button>
            </div>
        </div>
    @endif

    @if ($closing->observations)
        <p class="text-xs text-gray-500 bg-gray-50 rounded-lg p-3">{{ $closing->observations }}</p>
    @endif
    @endif
</div>
    </div>
</div>
{{-- Mobile --}}
<div class="lg:hidden">
<div class="p-3 space-y-4">
    @if ($noOutlet)
        <p class="text-center text-sm text-gray-400 py-10 px-4">
            Aucun point de vente configuré pour votre société.
            @if (auth()->user()->role === \App\Enums\UserRole::ADMIN_COMPANY)
                <a href="{{ route('admin.index') }}" wire:navigate class="text-orange-600 font-medium">Créer un point de vente</a>
            @endif
        </p>
    @else
    <div class="flex items-center justify-between">
        <h1 class="text-base font-semibold text-gray-900">Point de journée — {{ $closing->business_date }}</h1>
        <x-status-badge
            :status="match($closing->status->value) { 'VALIDATED' => 'green', 'PENDING_VALIDATION' => 'orange', 'REJECTED' => 'red', default => 'gray' }"
            :label="$closing->status->label()"
        />
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-2">
        <p class="text-sm font-semibold text-gray-900">Ventes par mode de paiement</p>
        @foreach (['cash' => 'Espèces', 'mobile_money' => 'Mobile Money', 'bank_transfer' => 'Virement', 'check' => 'Chèque', 'customer_credit' => 'Crédit client', 'other' => 'Autre'] as $key => $label)
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">{{ $label }}</span>
                <span class="text-gray-900"><x-money :amount="$this->summary[$key]" /></span>
            </div>
        @endforeach
        <div class="border-t border-gray-100 pt-2 flex justify-between text-sm font-semibold">
            <span>Total encaissé</span>
            <span><x-money :amount="$this->summary['total']" /></span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2">
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Anciennes créances encaissées</p>
            <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->oldReceivablesCollected" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Remises accordées</p>
            <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->discounts" /></p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Factures annulées</p>
            <p class="text-sm font-semibold text-gray-900">{{ $this->cancelledInvoices }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3">
            <p class="text-xs text-gray-400">Produits livrés</p>
            <p class="text-sm font-semibold text-gray-900">{{ number_format($this->deliveredProducts / 100, 0, ',', ' ') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-3 col-span-2">
            <p class="text-xs text-gray-400">Commandes non livrées</p>
            <p class="text-sm font-semibold text-gray-900">{{ $this->undeliveredOrders }}</p>
        </div>
    </div>

    @if (in_array($closing->status, [\App\Enums\DailyClosingStatus::OPEN, \App\Enums\DailyClosingStatus::REJECTED], true))
        <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <div>
                <label class="text-sm font-medium text-gray-700">Montant physique en caisse</label>
                <input type="number" min="0" wire:model.live="declaredCash" class="mt-1 block w-full rounded-lg border-gray-200">
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Différence</span>
                <span class="font-semibold {{ $this->difference !== 0 ? 'text-red-600' : 'text-green-600' }}">
                    <x-money :amount="$this->difference" />
                </span>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Observation</label>
                <textarea wire:model="observations" rows="2" class="mt-1 block w-full rounded-lg border-gray-200"></textarea>
            </div>

            <button type="button" wire:click="close" class="w-full rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5">
                Fermer la journée
            </button>
        </div>
    @endif

    @if ($this->canValidate && $closing->status === \App\Enums\DailyClosingStatus::PENDING_VALIDATION)
        <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-3">
            <p class="text-sm font-semibold text-gray-900">Validation responsable</p>
            <div class="flex gap-2">
                <button type="button" wire:click="requestValidate" class="flex-1 rounded-lg bg-green-600 text-white text-sm font-medium py-2.5">
                    Valider
                </button>
            </div>
            <div>
                <textarea wire:model="rejectReason" rows="2" placeholder="Motif de rejet" class="block w-full rounded-lg border-gray-200"></textarea>
                @error('rejectReason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <button type="button" wire:click="reject" class="mt-2 w-full rounded-lg bg-red-600 text-white text-sm font-medium py-2.5">
                    Rejeter
                </button>
            </div>
        </div>
    @endif

    @if ($closing->observations)
        <p class="text-xs text-gray-500 bg-gray-50 rounded-lg p-3">{{ $closing->observations }}</p>
    @endif
    @endif
</div>
</div>
</div>
