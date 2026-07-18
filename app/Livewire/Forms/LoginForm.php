<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $phone = '';

    public string $countryCode = '+225';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = true;

    public function ajouterChiffre(string $d): void
    {
        if (strlen($this->password) < 4) {
            $this->password .= $d;
        }
    }

    public function effacerChiffre(): void
    {
        $this->password = substr($this->password, 0, -1);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $digits = preg_replace('/\D/', '', $this->phone);
        $codeDigits = preg_replace('/\D/', '', $this->countryCode);
        // Avoid double prefix when the input already includes the country code
        if (str_starts_with($digits, $codeDigits)) {
            $phone = '+' . $digits;
        } else {
            $phone = $this->countryCode . $digits;
        }

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['phone' => $phone, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            $this->password = '';

            throw ValidationException::withMessages([
                'form.password' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.password' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->countryCode . $this->phone) . '|' . request()->ip());
    }
}
