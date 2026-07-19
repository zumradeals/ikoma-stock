<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton (une seule ligne, id=1) : réglages plateforme éditables depuis
 * l'espace SUPER_ADMIN. Voir App\Providers\AppServiceProvider pour
 * l'application au runtime de la config mail.*.
 */
class PlatformSetting extends Model
{
    public const DEFAULT_APP_NAME    = 'Ikoma Stock';
    public const DEFAULT_APP_TAGLINE = 'Votre boutique, simplement.';

    protected $fillable = [
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'mail_from_name',
        'app_name', 'app_tagline', 'app_logo_path',
    ];

    protected function casts(): array
    {
        return [
            'mail_port' => 'integer',
            'mail_password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function resolvedAppName(): string
    {
        return filled($this->app_name) ? $this->app_name : self::DEFAULT_APP_NAME;
    }

    public function resolvedAppTagline(): string
    {
        return filled($this->app_tagline) ? $this->app_tagline : self::DEFAULT_APP_TAGLINE;
    }

    public function isMailConfigured(): bool
    {
        return filled($this->mail_host) && filled($this->mail_from_address);
    }
}
