<?php

namespace App\Livewire\Platform;

use App\Models\PlatformSetting;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['bareDesktop' => true])]
class PlatformSettingsForm extends Component
{
    public ?int $mailPort = 587;

    public string $mailHost = '';

    public string $mailUsername = '';

    public string $mailPassword = '';

    public string $mailEncryption = 'tls';

    public string $mailFromAddress = '';

    public string $mailFromName = '';

    public bool $saved = false;

    public function mount(): void
    {
        $this->authorize('manage', PlatformSetting::class);

        $settings = PlatformSetting::current();

        $this->mailHost = $settings->mail_host ?? '';
        $this->mailPort = $settings->mail_port ?? 587;
        $this->mailUsername = $settings->mail_username ?? '';
        $this->mailEncryption = $settings->mail_encryption ?? 'tls';
        $this->mailFromAddress = $settings->mail_from_address ?? '';
        $this->mailFromName = $settings->mail_from_name ?? '';
        // mailPassword volontairement laissé vide : on ne réaffiche jamais un secret déjà stocké.
    }

    public function save(): void
    {
        $this->authorize('manage', PlatformSetting::class);

        $this->validate([
            'mailHost' => 'nullable|string|max:255',
            'mailPort' => 'nullable|integer|min:1|max:65535',
            'mailUsername' => 'nullable|string|max:255',
            'mailPassword' => 'nullable|string|max:255',
            'mailEncryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mailFromAddress' => 'nullable|email|max:255',
            'mailFromName' => 'nullable|string|max:255',
        ]);

        $settings = PlatformSetting::current();

        $attributes = [
            'mail_host' => $this->mailHost ?: null,
            'mail_port' => $this->mailPort ?: null,
            'mail_username' => $this->mailUsername ?: null,
            'mail_encryption' => $this->mailEncryption ?: null,
            'mail_from_address' => $this->mailFromAddress ?: null,
            'mail_from_name' => $this->mailFromName ?: null,
        ];

        if ($this->mailPassword !== '') {
            $attributes['mail_password'] = $this->mailPassword;
        }

        $settings->update($attributes);

        $this->mailPassword = '';
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.platform.platform-settings-form');
    }
}
