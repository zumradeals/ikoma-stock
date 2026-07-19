<div>
{{-- Desktop --}}
<div class="hidden lg:flex h-screen overflow-hidden bg-cream">
    <x-ikoma.desktop-sidebar active="quotes" />
    <div class="flex-1 overflow-y-auto">
        <div class="sticky top-0 z-10 bg-white border-b border-line px-5 py-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('quotes.index') }}" wire:navigate class="text-xs text-brand font-bold shrink-0">← Devis</a>
                    <div class="min-w-0">
                        <h1 class="text-base font-extrabold text-ink">{{ $quote->number }}</h1>
                        <p class="text-xs text-ink-soft">{{ \App\Support\HumanDate::format($quote->created_at) }}</p>
                    </div>
                </div>
                <span class="shrink-0 text-[11px] font-bold rounded-full px-3 py-1 {{ $quote->status->badgeClass() }}">
                    {{ $quote->status->label() }}
                </span>
            </div>
        </div>
        <div class="p-4 max-w-2xl space-y-4">
            @include('livewire.quotes._detail-body')
        </div>
    </div>
</div>

{{-- Mobile --}}
<div class="lg:hidden">
    <div class="sticky top-0 z-10 bg-white border-b border-line px-4 py-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('quotes.index') }}" wire:navigate class="text-xs text-brand font-bold shrink-0">← Devis</a>
                <div class="min-w-0">
                    <h1 class="text-base font-extrabold text-ink">{{ $quote->number }}</h1>
                    <p class="text-xs text-ink-soft">{{ \App\Support\HumanDate::format($quote->created_at) }}</p>
                </div>
            </div>
            <span class="shrink-0 text-[11px] font-bold rounded-full px-3 py-1 {{ $quote->status->badgeClass() }}">
                {{ $quote->status->label() }}
            </span>
        </div>
    </div>
    <div class="p-4 space-y-4">
        @include('livewire.quotes._detail-body')
    </div>
</div>
</div>
