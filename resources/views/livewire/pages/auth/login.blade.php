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
        <!-- Téléphone -->
        <div>
            <x-input-label for="phone" value="Numéro de téléphone" />
            <x-text-input
                wire:model="form.phone"
                id="phone"
                class="block mt-1 w-full"
                type="tel"
                inputmode="tel"
                name="phone"
                required
                autofocus
                autocomplete="tel"
            />
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
