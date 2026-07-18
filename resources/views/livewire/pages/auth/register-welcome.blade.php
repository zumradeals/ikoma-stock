<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public function go(): void
    {
        $this->redirect(route(auth()->user()->role->landingRoute()), navigate: true);
    }
}; ?>

<div class="space-y-6 text-center">

    {{-- Icône succès --}}
    <div class="flex flex-col items-center gap-3">
        <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-brand text-white text-3xl shadow-brand-glow">
            ✓
        </div>
        <h1 class="text-xl font-extrabold text-ink">
            Bienvenue, {{ Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }} !
        </h1>
        <p class="text-sm text-ink-soft">
            Ton compte <span class="font-bold text-ink">{{ auth()->user()->company?->name }}</span> est prêt.
        </p>
    </div>

    {{-- CTA --}}
    <button
        type="button"
        wire:click="go"
        class="inline-flex items-center justify-center w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm text-white bg-brand shadow-brand-glow hover:brightness-90 active:brightness-75 transition"
    >
        Commencer →
    </button>

</div>
