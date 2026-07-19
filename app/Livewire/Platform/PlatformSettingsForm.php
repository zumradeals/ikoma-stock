<?php

namespace App\Livewire\Platform;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app', ['bareDesktop' => true])]
class PlatformSettingsForm extends Component
{
    use WithFileUploads;

    // ── Branding ──────────────────────────────────────────────────────────────
    public string $appName = '';

    public string $appTagline = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $appLogo = null;

    public ?string $currentLogoPath = null;

    // ── Mail ──────────────────────────────────────────────────────────────────
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

        $this->appName        = $settings->app_name ?? '';
        $this->appTagline     = $settings->app_tagline ?? '';
        $this->currentLogoPath = $settings->app_logo_path;

        $this->mailHost        = $settings->mail_host ?? '';
        $this->mailPort        = $settings->mail_port ?? 587;
        $this->mailUsername    = $settings->mail_username ?? '';
        $this->mailEncryption  = $settings->mail_encryption ?? 'tls';
        $this->mailFromAddress = $settings->mail_from_address ?? '';
        $this->mailFromName    = $settings->mail_from_name ?? '';
        // mailPassword volontairement laissé vide : on ne réaffiche jamais un secret déjà stocké.
    }

    public function removeLogo(): void
    {
        $this->authorize('manage', PlatformSetting::class);

        $settings = PlatformSetting::current();
        if ($settings->app_logo_path) {
            Storage::disk('public')->delete($settings->app_logo_path);
            $settings->update(['app_logo_path' => null]);
        }
        $this->currentLogoPath = null;
    }

    public function save(): void
    {
        $this->authorize('manage', PlatformSetting::class);

        $this->validate([
            'appName'          => 'nullable|string|max:100',
            'appTagline'       => 'nullable|string|max:200',
            'appLogo'          => 'nullable|image|max:2048',
            'mailHost'         => 'nullable|string|max:255',
            'mailPort'         => 'nullable|integer|min:1|max:65535',
            'mailUsername'     => 'nullable|string|max:255',
            'mailPassword'     => 'nullable|string|max:255',
            'mailEncryption'   => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mailFromAddress'  => 'nullable|email|max:255',
            'mailFromName'     => 'nullable|string|max:255',
        ]);

        $settings = PlatformSetting::current();

        $attributes = [
            'app_name'         => $this->appName ?: null,
            'app_tagline'      => $this->appTagline ?: null,
            'mail_host'        => $this->mailHost ?: null,
            'mail_port'        => $this->mailPort ?: null,
            'mail_username'    => $this->mailUsername ?: null,
            'mail_encryption'  => $this->mailEncryption ?: null,
            'mail_from_address' => $this->mailFromAddress ?: null,
            'mail_from_name'   => $this->mailFromName ?: null,
        ];

        if ($this->appLogo) {
            if ($settings->app_logo_path) {
                Storage::disk('public')->delete($settings->app_logo_path);
            }
            $attributes['app_logo_path'] = $this->appLogo->store('platform/logos', 'public');
            $this->currentLogoPath = $attributes['app_logo_path'];
            $this->appLogo = null;
        }

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
