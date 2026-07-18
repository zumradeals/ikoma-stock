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
    public string $step = 'phone';

    #[Validate('required|string')]
    public string $phone = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = true;

    public function goToPinStep(): void
    {
        $this->validateOnly('phone');
        $this->step = 'pin';
    }

    public function backToPhoneStep(): void
    {
        $this->step = 'phone';
        $this->password = '';
        $this->resetErrorBag();
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        // Normalise avant rate-limit et tentative
        $this->phone = preg_replace('/\s+/', '', $this->phone);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['phone' => $this->phone, 'password' => $this->password], $this->remember)) {
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

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->phone).'|'.request()->ip());
    }
}
