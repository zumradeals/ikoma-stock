<div>
    @if ($show)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 px-4" wire:key="confirmation-modal">
            <div class="w-full sm:max-w-sm bg-white rounded-t-2xl sm:rounded-xl shadow-xl p-5 mb-0 sm:mb-auto">
                <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>

                @if ($detail)
                    <p class="mt-2 text-xs text-gray-500 bg-gray-50 rounded-md p-2">{{ $detail }}</p>
                @endif

                <div class="mt-5 flex gap-3">
                    <button
                        type="button"
                        wire:click="cancel"
                        class="flex-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium py-2.5"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        wire:click="confirm"
                        class="flex-1 rounded-lg text-white text-sm font-medium py-2.5 {{ $danger ? 'bg-red-600' : 'bg-orange-600' }}"
                    >
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
