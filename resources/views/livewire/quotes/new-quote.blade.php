<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="quotes" />
    <div class="flex-1 overflow-y-auto">
        <div class="sticky top-0 z-10 bg-white border-b border-line px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('quotes.index') }}" wire:navigate class="text-xs text-brand font-bold">← Devis</a>
                <h1 class="text-base font-extrabold text-ink">Nouveau devis</h1>
            </div>
        </div>
        <div class="p-4 max-w-2xl">
            @include('livewire.quotes._form')
        </div>
    </div>
</div>

{{-- Mobile --}}
<div class="lg:hidden">
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 flex items-center gap-3">
        <a href="{{ route('quotes.index') }}" wire:navigate class="text-xs text-brand font-bold">← Devis</a>
        <h1 class="text-base font-extrabold text-ink">Nouveau devis</h1>
    </div>
    <div class="p-4">
        @include('livewire.quotes._form')
    </div>
</div>
</div>
