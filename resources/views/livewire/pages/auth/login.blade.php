<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public LoginForm $form;

    /**
     * Traite la demande de connexion.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();
        Session::put('current_company_id', $user->company_id);

        $this->redirectIntended(default: route($user->role->landingRoute(), absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Téléphone avec sélecteur de pays -->
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
                    const lang = navigator.language.toUpperCase();
                    const map = {CI:'CI',SN:'SN',ML:'ML',BF:'BF',GN:'GN',TG:'TG',BJ:'BJ',NE:'NE',GH:'GH',NG:'NG',CM:'CM',FR:'FR',BE:'BE'};
                    const country = lang.split('-').pop();
                    const found = this.countries.find(c => c.code === country);
                    if (found) this.dial = found.dial;
                }
                this.sync();
            }
        }" x-init="detect()">
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
                    id="phone"
                    type="tel"
                    inputmode="numeric"
                    placeholder="0718713781"
                    required
                    autofocus
                    autocomplete="tel-national"
                    class="border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm block w-full"
                />
            </div>
            <x-input-error :messages="$errors->get('form.phone')" class="mt-2" />
        </div>

        <!-- Code (mot de passe) -->
        <div class="mt-4">
            <x-input-label for="password" value="Code" />
            <x-text-input
                wire:model="form.password"
                id="password"
                class="block mt-1 w-full"
                type="password"
                inputmode="numeric"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Se souvenir de moi -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Se souvenir de moi</span>
            </label>
        </div>

        <div class="mt-4 space-y-3">
            <x-ikoma.button-primary type="submit" class="w-full justify-center">
                Entrer
            </x-ikoma.button-primary>

            @if (Route::has('password.request'))
                <p class="text-center text-sm text-gray-500">
                    Code oublié ?
                    <a class="underline text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 rounded-sm" href="{{ route('password.request') }}" wire:navigate>
                        Contacte ton responsable
                    </a>
                </p>
            @endif
        </div>
    </form>
</div>
