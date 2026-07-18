<?php

use App\Exceptions\Business\PhoneAlreadyRegisteredException;
use App\Modules\Company\Services\CompanyOnboardingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $pinStep = 'choose'; // 'choose' | 'confirm'
    public string $pin1 = '';
    public string $pin2 = '';
    public string $pinError = '';

    public function mount(): void
    {
        if (! session()->has('onboarding')) {
            $this->redirect(route('register.identity'), navigate: true);
        }
    }

    public function ajouterChiffre(string $d): void
    {
        $this->pinError = '';

        if ($this->pinStep === 'choose') {
            if (strlen($this->pin1) < 4) {
                $this->pin1 .= $d;
                if (strlen($this->pin1) === 4) {
                    $this->pinStep = 'confirm';
                }
            }
        } else {
            if (strlen($this->pin2) < 4) {
                $this->pin2 .= $d;
            }
        }
    }

    public function effacerChiffre(): void
    {
        $this->pinError = '';

        if ($this->pinStep === 'confirm') {
            if (strlen($this->pin2) > 0) {
                $this->pin2 = substr($this->pin2, 0, -1);
            } else {
                $this->pinStep = 'choose';
                $this->pin1 = '';
            }
        } else {
            $this->pin1 = substr($this->pin1, 0, -1);
        }
    }

    public function confirm(): void
    {
        if ($this->pin1 !== $this->pin2) {
            $this->pin2 = '';
            $this->pinError = 'Les codes PIN ne correspondent pas. Réessaie.';
            return;
        }

        $data = session('onboarding');

        try {
            $user = app(CompanyOnboardingService::class)->register(
                fullName: $data['name'],
                phone: $data['phone'],
                password: $this->pin1,
                companyName: $data['companyName'],
                outletName: $data['outletName'],
            );
        } catch (PhoneAlreadyRegisteredException $e) {
            session()->forget('onboarding');
            session()->flash('onboarding_error', 'Ce numéro de téléphone est déjà utilisé. Essaie de te connecter.');
            $this->redirect(route('register.identity'), navigate: true);
            return;
        }

        session()->forget('onboarding');
        Session::regenerate();
        Auth::login($user);
        Session::put('current_company_id', $user->company_id);

        $this->redirect(route('register.welcome'));
    }
}; ?>

<div class="space-y-5">

    {{-- En-tête --}}
    <div class="text-center">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand text-white text-xl font-extrabold mb-3 shadow-brand-glow">IK</div>
        <h1 class="text-lg font-extrabold text-ink">
            {{ $pinStep === 'choose' ? 'Choisis ton code PIN' : 'Confirme ton code PIN' }}
        </h1>
        <p class="text-sm text-ink-soft">
            {{ $pinStep === 'choose' ? '4 chiffres pour sécuriser ton compte' : 'Saisis à nouveau le même code' }}
        </p>
    </div>

    {{-- Indicateur de sous-étape --}}
    <div class="flex items-center justify-center gap-2">
        <div @class([
            'h-1.5 w-10 rounded-full transition-all',
            'bg-brand' => true,
        ])></div>
        <div @class([
            'h-1.5 w-10 rounded-full transition-all',
            'bg-brand' => $pinStep === 'confirm',
            'bg-line'  => $pinStep === 'choose',
        ])></div>
    </div>

    {{-- Erreur PIN mismatch --}}
    @if ($pinError)
        <div class="rounded-xl bg-red-50 border border-red-100 px-3 py-2.5 text-sm text-red-700 text-center">
            {{ $pinError }}
        </div>
    @endif

    {{-- 4 pastilles --}}
    @php $currentPin = $pinStep === 'choose' ? $pin1 : $pin2; $pinLen = strlen($currentPin); @endphp
    <div class="flex justify-center gap-3">
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

    {{-- Bouton final (étape confirm uniquement) --}}
    @if ($pinStep === 'confirm')
        @php $canConfirm = strlen($pin2) === 4; @endphp
        <button
            type="button"
            wire:click="confirm"
            @class([
                'inline-flex items-center justify-center w-full rounded-2xl px-4 py-3.5 font-extrabold text-sm transition',
                'text-white bg-brand shadow-brand-glow hover:brightness-90 active:brightness-75' => $canConfirm,
                'text-ink-soft bg-cream cursor-not-allowed' => ! $canConfirm,
            ])
            @disabled(! $canConfirm)
        >
            Créer mon compte
        </button>
    @endif

</div>
