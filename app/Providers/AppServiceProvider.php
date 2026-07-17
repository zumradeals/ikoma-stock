<?php

namespace App\Providers;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyPlatformMailSettings();
    }

    /**
     * Le SMTP se configure depuis l'écran SUPER_ADMIN (Paramètres
     * plateforme), pas en dur dans .env — voir PlatformSetting. Tant que
     * rien n'est renseigné, .env (MAIL_MAILER=log par défaut) reste actif.
     * Avalé silencieusement si la table n'existe pas encore (avant la
     * première migration, ex. pendant `artisan migrate` lui-même).
     */
    protected function applyPlatformMailSettings(): void
    {
        try {
            if (! Schema::hasTable('platform_settings')) {
                return;
            }

            $settings = PlatformSetting::query()->first();
        } catch (Throwable) {
            return;
        }

        if (! $settings || ! $settings->isMailConfigured()) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings->mail_host);
        Config::set('mail.mailers.smtp.port', $settings->mail_port);
        Config::set('mail.mailers.smtp.username', $settings->mail_username);
        Config::set('mail.mailers.smtp.password', $settings->mail_password);
        Config::set('mail.mailers.smtp.encryption', $settings->mail_encryption ?: null);
        Config::set('mail.from.address', $settings->mail_from_address);
        Config::set('mail.from.name', $settings->mail_from_name ?: config('app.name'));
    }
}
