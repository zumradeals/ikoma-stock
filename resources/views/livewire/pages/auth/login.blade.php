<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    public function goToPin(): void
    {
        $this->form->goToPinStep();
    }

    public function back(): void
    {
        $this->form->backToPhoneStep();
    }

    public function ajouterChiffre(string $d): void
    {
        if (strlen($this->form->password) >= 4) {
            return;
        }

        $this->form->password .= $d;

        if (strlen($this->form->password) === 4) {
            $this->login();
        }
    }

    public function effacer(): void
    {
        $this->form->password = substr($this->form->password, 0, -1);
    }

    public function login(): void
    {
        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();
        Session::put('current_company_id', $user->company_id);

        $this->redirectIntended(default: route($user->role->landingRoute(), absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- ===== ÉTAPE 1 : TÉLÉPHONE ===== --}}
    @if ($form->step === 'phone')
    <div class="text-center mb-8">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-[20px] bg-brand text-white text-2xl font-extrabold mb-4">IK</div>
        <h1 class="text-xl font-extrabold text-ink">Ikoma Stock</h1>
        <p class="text-sm text-ink-soft mt-1">Gère ta boutique en toute simplicité.</p>
    </div>

    <div x-data="{
        dial: '+225',
        local: '',
        countries: [
            {code:'CI', dial:'+225', flag:'🇨🇮', name:'Côte d\'Ivoire'},
            {code:'SN', dial:'+221', flag:'🇸🇳', name:'Sénégal'},
            {code:'ML', dial:'+223', flag:'🇲🇱', name:'Mali'},
            {code:'BF', dial:'+226', flag:'🇧🇫', name:'Burkina Faso'},
            {code:'GN', dial:'+224', flag:'🇬🇳', name:'Guinée'},
            {code:'TG', dial:'+228', flag:'🇹🇬', name:'Togo'},
            {code:'BJ', dial:'+229', flag:'🇧🇯', name:'Bénin'},
            {code:'NE', dial:'+227', flag:'🇳🇪', name:'Niger'},
            {code:'GH', dial:'+233', flag:'🇬🇭', name:'Ghana'},
            {code:'NG', dial:'+234', flag:'🇳🇬', name:'Nigeria'},
            {code:'CM', dial:'+237', flag:'🇨🇲', name:'Cameroun'},
            {code:'FR', dial:'+33',  flag:'🇫🇷', name:'France'},
            {code:'BE', dial:'+32',  flag:'🇧🇪', name:'Belgique'},
        ],
        get full() { return this.dial + this.local.replace(/\s+/g,''); },
        sync() { @this.set('form.phone', this.full); },
        detect() {
            if (navigator.language) {
                const country = navigator.language.toUpperCase().split('-').pop();
                const found = this.countries.find(c => c.code === country);
                if (found) this.dial = found.dial;
            }
            this.sync();
        }
    }" x-init="detect()" class="space-y-4">
        <div>
            <x-input-label for="phone" value="Numéro de téléphone" />
            <div class="flex mt-1 gap-2">
                <select
                    x-model="dial"
                    @change="sync()"
                    class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm text-sm flex-none w-36"
                >
                    <template x-for="c in countries" :key="c.code">
                        <option :value="c.dial" x-text="c.flag + ' ' + c.dial" :selected="c.dial === dial"></option>
                    </template>
                </select>
                <input
                    x-model="local"
                    @input="sync()"
                    @keydown.enter.prevent="if (local.length > 0) $wire.goToPin()"
                    id="phone"
                    type="tel"
                    inputmode="numeric"
                    placeholder="0718713781"
                    autofocus
                    autocomplete="tel-national"
                    class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block w-full"
                />
            </div>
            <x-input-error :messages="$errors->get('form.phone')" class="mt-2" />
        </div>

        <p class="text-xs text-ink-soft">Le responsable t'a donné un code secret à 4 chiffres.</p>

        <x-ikoma.button-primary
            wire:click="goToPin"
            x-bind:disabled="local.length === 0"
            x-bind:class="local.length === 0 ? 'opacity-40 cursor-not-allowed' : ''"
            class="w-full justify-center"
        >
            Continuer
        </x-ikoma.button-primary>
    </div>
    @endif

    {{-- ===== ÉTAPE 2 : PIN ===== --}}
    @if ($form->step === 'pin')
    @php $pinLen = strlen($form->password); @endphp
    <div class="text-center mb-6">
        <h1 class="text-xl font-extrabold text-ink">Bienvenue 👋</h1>
        <p class="text-sm text-ink-soft mt-1">Entre ton code secret</p>
    </div>

    {{-- Message d'erreur --}}
    @if ($errors->has('form.password'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 text-center">
            {{ $errors->first('form.password') }}
        </div>
    @endif

    {{-- 4 points --}}
    <div class="flex justify-center gap-4 mb-8">
        @for ($i = 0; $i < 4; $i++)
            <div class="h-5 w-5 rounded-full border-2 transition-all duration-150
                {{ $i < $pinLen ? 'bg-brand border-brand' : 'border-gray-300' }}">
            </div>
        @endfor
    </div>

    {{-- Pavé numérique --}}
    <div class="grid grid-cols-3 gap-3 max-w-[280px] mx-auto">
        @foreach ([1,2,3,4,5,6,7,8,9] as $d)
            <button
                type="button"
                wire:click="ajouterChiffre('{{ $d }}')"
                class="h-16 rounded-2xl bg-white border border-line text-xl font-bold text-ink shadow-sm active:bg-cream transition"
            >{{ $d }}</button>
        @endforeach

        {{-- Ligne du bas : vide, 0, effacer --}}
        <div></div>
        <button
            type="button"
            wire:click="ajouterChiffre('0')"
            class="h-16 rounded-2xl bg-white border border-line text-xl font-bold text-ink shadow-sm active:bg-cream transition"
        >0</button>
        <button
            type="button"
            wire:click="effacer"
            class="h-16 rounded-2xl bg-white border border-line text-xl font-bold text-ink shadow-sm active:bg-cream transition flex items-center justify-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
            </svg>
        </button>
    </div>

    <div class="mt-6 text-center">
        <button type="button" wire:click="back" class="text-sm text-ink-soft underline underline-offset-2">
            Changer de numéro
        </button>
    </div>
    @endif
</div>
