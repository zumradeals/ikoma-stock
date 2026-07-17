<div class="p-4 space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-4">
        <p class="text-sm text-gray-500">Facture {{ $this->invoice->number }}</p>
        <div class="grid grid-cols-3 gap-2 mt-2 text-center">
            <div>
                <p class="text-xs text-gray-400">Total</p>
                <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->invoice->total_amount" /></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Déjà payé</p>
                <p class="text-sm font-semibold text-gray-900"><x-money :amount="$this->invoice->paid_amount" /></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Reste à payer</p>
                <p class="text-sm font-semibold text-red-600"><x-money :amount="$this->invoice->balance_due" /></p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-4">
        @error('form') <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $message }}</p> @enderror

        <div>
            <label class="text-sm font-medium text-gray-700">Montant</label>
            <input type="number" min="1" wire:model="amount" class="mt-1 block w-full rounded-lg border-gray-200">
            @error('amount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Mode de paiement</label>
            <select wire:model="method" class="mt-1 block w-full rounded-lg border-gray-200">
                @foreach ($this->methods as $m)
                    <option value="{{ $m->value }}">{{ $m->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Référence (optionnelle)</label>
            <input type="text" wire:model="reference" class="mt-1 block w-full rounded-lg border-gray-200">
        </div>

        <div>
            <label class="text-sm font-medium text-gray-700">Preuve de paiement (photo)</label>
            <input type="file" wire:model="proof" accept="image/*" class="mt-1 block w-full text-sm">
            @error('proof') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full rounded-lg bg-orange-600 text-white text-sm font-medium py-2.5">
            Enregistrer le paiement
        </button>
    </form>
</div>
