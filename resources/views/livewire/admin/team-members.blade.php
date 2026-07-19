<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="team" />
    <div class="flex-1 overflow-y-auto">

        <div class="sticky top-0 z-10 bg-white border-b border-line px-5 py-3 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-base font-extrabold text-ink">Équipe</h1>
                <p class="text-xs text-ink-soft">{{ $this->team->count() }} membre{{ $this->team->count() > 1 ? 's' : '' }}</p>
            </div>
            <button type="button" wire:click="openForm"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 hover:brightness-90 active:brightness-75 transition">
                <span class="text-base font-bold leading-none">+</span> Ajouter un membre
            </button>
        </div>

        <div class="p-4 space-y-3 max-w-2xl">
            @include('livewire.admin._team-body')
        </div>
    </div>
</div>

{{-- Mobile --}}
<div class="lg:hidden">
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-base font-extrabold text-ink">Équipe</h1>
            <p class="text-xs text-ink-soft">{{ $this->team->count() }} membre{{ $this->team->count() > 1 ? 's' : '' }}</p>
        </div>
        <button type="button" wire:click="openForm"
                class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-brand text-white text-sm font-extrabold px-4 py-2.5 hover:brightness-90 transition">
            <span class="text-base font-bold leading-none">+</span> Ajouter
        </button>
    </div>
    <div class="p-4 space-y-3">
        @include('livewire.admin._team-body')
    </div>
</div>
</div>
