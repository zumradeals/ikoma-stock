<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth', ['bareHeader' => true])] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();
        Session::put('current_company_id', $user->company_id);

        $this->redirectIntended(default: route($user->role->landingRoute(), absolute: false), navigate: true);
    }

    public function ajouterChiffre(string $d): void
    {
        $this->form->ajouterChiffre($d);
    }

    public function effacerChiffre(): void
    {
        $this->form->effacerChiffre();
    }
}; ?>

<div class="space-y-5">

    {{-- En-tête --}}
    <div class="text-center">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand text-white text-xl font-extrabold mb-3 shadow-brand-glow">IK</div>
        <h1 class="text-lg font-extrabold text-ink">Ikoma Stock</h1>
        <p class="text-sm text-ink-soft">Connecte-toi avec ton téléphone</p>
    </div>

    {{-- Onglets --}}
    <div class="flex rounded-xl bg-cream p-1 gap-1">
        <button type="button" class="flex-1 rounded-lg py-2 text-sm font-bold text-white bg-brand shadow-sm">
            Connexion
        </button>
        <a href="{{ route('register.identity') }}" wire:navigate
           class="flex-1 rounded-lg py-2 text-sm font-semibold text-ink-soft text-center hover:text-ink transition">
            Créer mon entreprise
        </a>
    </div>

    {{-- Champ téléphone --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-1.5">Téléphone</label>
        <div class="flex rounded-xl border border-line bg-white overflow-hidden focus-within:border-brand transition">
            {{-- Sélecteur indicatif --}}
            <div class="flex-none">
                <select
                    wire:model="form.countryCode"
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
                wire:model="form.phone"
                type="tel"
                inputmode="numeric"
                placeholder="0718713781"
                autocomplete="tel-national"
                class="flex-1 border-0 px-3 py-3 text-sm text-ink placeholder-ink-soft/50 focus:ring-0 focus:outline-none bg-transparent"
            />
        </div>
        <x-input-error :messages="$errors->get('form.phone')" class="mt-1.5" />
    </div>

    {{-- Section PIN --}}
    <div>
        <label class="block text-xs font-bold text-ink-soft uppercase tracking-widest mb-3">Ton code PIN (4 chiffres)</label>

        {{-- Message d'erreur --}}
        @if ($errors->has('form.password'))
            <div class="mb-3 rounded-xl bg-red-50 border border-red-100 px-3 py-2.5 text-sm text-red-700 text-center">
                {{ $errors->first('form.password') }}
            </div>
        @endif

        {{-- 4 pastilles --}}
        @php $pinLen = strlen($form->password); @endphp
        <div class="flex justify-center gap-3 mb-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="h-4 w-4 rounded-full border-2 transition-all duration-100
                    {{ $i < $pinLen ? 'bg-brand border-brand scale-110' : 'border-gray-300' }}">
                </div>
            @endfor
        </div>

        {{-- Pavé numérique --}}
        <div class="grid grid-cols-3 gap-2">
            @foreach ([1,2,3,4,5,6,7,8,9] as $d)
                <button
                    type="button"
                    wire:click="ajouterChiffre('{{ $d }}')"
                    class="h-14 rounded-xl bg-cream text-lg font-bold text-ink active:bg-line transition select-none"
                >{{ $d }}</button>
            @endforeach
            <div></div>
            <button
                type="button"
                wire:click="ajouterChiffre('0')"
                class="h-14 rounded-xl bg-cream text-lg font-bold text-ink active:bg-line transition select-none"
            >0</button>
            <button
                type="button"
                wire:click="effacerChiffre"
                class="h-14 rounded-xl bg-cream text-ink active:bg-line transition flex items-center justify-center select-none"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Bouton connexion --}}
    @php $canSubmit = strlen(trim($form->phone)) > 0 && strlen($form->password) === 4; @endphp
    <button
        type="button"
        wire:click="login"
        @class([
            'inline-flex items-center justify-center w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm transition',
            'text-white bg-brand shadow-brand-glow hover:brightness-90 active:brightness-75' => $canSubmit,
            'text-ink-soft bg-cream cursor-not-allowed' => ! $canSubmit,
        ])
        @disabled(! $canSubmit)
    >
        Se connecter
    </button>

</div>
