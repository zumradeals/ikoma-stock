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
    protected $fillable = [
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'mail_from_name',
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

    public function isMailConfigured(): bool
    {
        return filled($this->mail_host) && filled($this->mail_from_address);
    }
}
