<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';
    public string $countryCode = '+225';
    public string $phone = '';
    public string $companyName = '';
    public string $outletName = '';

    public function proceed(): void
    {
        $this->validate([
            'name'        => 'required|string|max:255',
            'countryCode' => 'required|string',
            'phone'       => 'required|string|min:6',
            'companyName' => 'nullable|string|max:255',
            'outletName'  => 'nullable|string|max:255',
        ]);

        $digits = preg_replace('/\D/', '', $this->phone);
        $codeDigits = preg_replace('/\D/', '', $this->countryCode);
        $fullPhone = str_starts_with($digits, $codeDigits)
            ? '+' . $digits
            : $this->countryCode . $digits;

        session()->put('onboarding', [
            'name'        => trim($this->name),
            'phone'       => $fullPhone,
            'companyName' => trim($this->companyName) ?: 'Mon entreprise',
            'outletName'  => trim($this->outletName) ?: 'Boutique principale',
        ]);

        // Redirect HTTP classique (pas navigate:true) pour que la session
        // soit persistée avant la prochaine requête GET.
        $this->redirect(route('register.pin'));
    }
}; ?>

{{--
    x-data tracks hasName/hasPhone via @input events — distinct de wire:model
    pour éviter le conflit entre Alpine x-model et Livewire wire:model.
--}}
<div class="space-y-5"
     x-data="{ hasName: false, hasPhone: false }">

    {{-- En-tête --}}
    <div class="text-center">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand text-white text-xl font-extrabold mb-3 shadow-brand-glow">IK</div>
        <h1 class="text-lg font-extrabold text-ink">Ikoma Stock</h1>
        <p class="text-sm text-ink-soft">Crée ton compte gratuitement</p>
    </div>

    {{-- Erreur flash (ex: téléphone déjà utilisé) --}}
    @if (session('onboarding_error'))
        <div class="rounded-xl bg-red-50 border border-red-100 px-3 py-2.5 text-sm text-red-700 text-center">
            {{ session('onboarding_error') }}
        </div>
    @endif

    {{-- Onglets --}}
    <div class="flex rounded-xl bg-cream p-1 gap-1">
        <a href="{{ route('login') }}" wire:navigate
           class="flex-1 rounded-lg py-2 text-sm font-semibold text-ink-soft text-center hover:text-ink transition">
            Connexion
        </a>
        <button type="button"
                class="flex-1 rounded-lg py-2 text-sm font-bold text-white bg-brand shadow-sm">
            Créer mon entreprise
        </button>
    </div>

    {{-- Nom complet --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1.5">Ton nom complet</label>
        <input
            wire:model="name"
            @input="hasName = $event.target.value.trim().length > 0"
            type="text"
            placeholder="ex: Awa Koné"
            autocomplete="name"
            class="w-full rounded-xl border border-line bg-white px-3 py-3 text-sm text-ink placeholder-ink-soft/50 focus:border-brand focus:ring-0 focus:outline-none transition"
        />
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    {{-- Téléphone --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1.5">Ton téléphone</label>
        <div class="flex rounded-xl border border-line bg-white overflow-hidden focus-within:border-brand transition">
            <div class="flex-none">
                <select
                    wire:model="countryCode"
                    class="h-full border-0 border-r border-line bg-cream text-sm font-bold text-ink px-3 focus:ring-0 focus:outline-none"
                >
                    <option value="+225">🇨🇮 +225</option>
                    <option value="+221">🇸🇳 +221</option>
                    <option value="+223">🇲🇱 +223</option>
                    <option value="+226">🇧🇫 +226</option>
                    <option value="+224">🇬🇳 +224</option>
                    <option value="+228">🇹🇬 +228</option>
                    <option value="+229">🇧🇯 +229</option>
                    <option value="+227">🇳🇪 +227</option>
                    <option value="+233">🇬🇭 +233</option>
                    <option value="+234">🇳🇬 +234</option>
                    <option value="+237">🇨🇲 +237</option>
                    <option value="+33">🇫🇷 +33</option>
                    <option value="+32">🇧🇪 +32</option>
                </select>
            </div>
            <input
                wire:model="phone"
                @input="hasPhone = $event.target.value.trim().length > 0"
                type="tel"
                inputmode="numeric"
                placeholder="0718713781"
                autocomplete="tel-national"
                class="flex-1 border-0 px-3 py-3 text-sm text-ink placeholder-ink-soft/50 focus:ring-0 focus:outline-none bg-transparent"
            />
        </div>
        <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
    </div>

    {{-- Nom de l'entreprise --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1.5">Nom de ton entreprise <span class="font-normal normal-case">(optionnel)</span></label>
        <input
            wire:model="companyName"
            type="text"
            placeholder="ex: Boutique Awa"
            class="w-full rounded-xl border border-line bg-white px-3 py-3 text-sm text-ink placeholder-ink-soft/50 focus:border-brand focus:ring-0 focus:outline-none transition"
        />
    </div>

    {{-- Nom du point de vente --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1.5">Nom du point de vente <span class="font-normal normal-case">(optionnel)</span></label>
        <input
            wire:model="outletName"
            type="text"
            placeholder="Boutique principale"
            class="w-full rounded-xl border border-line bg-white px-3 py-3 text-sm text-ink placeholder-ink-soft/50 focus:border-brand focus:ring-0 focus:outline-none transition"
        />
    </div>

    {{-- Bouton — Alpine gère l'état actif/inactif côté client via @input --}}
    <button
        type="button"
        wire:click="proceed"
        x-bind:disabled="!hasName || !hasPhone"
        x-bind:class="hasName && hasPhone
            ? 'text-white bg-brand shadow-brand-glow hover:brightness-90 active:brightness-75'
            : 'text-ink-soft bg-cream cursor-not-allowed'"
        class="inline-flex items-center justify-center w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm transition"
    >
        Continuer
    </button>

</div>
