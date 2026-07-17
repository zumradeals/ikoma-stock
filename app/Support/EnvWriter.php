<?php

namespace App\Support;

/**
 * Met à jour des clés du fichier .env sur disque (installeur web /install,
 * exécuté avant que la config ne soit mise en cache). Remplace la ligne
 * existante si la clé est déjà présente, l'ajoute sinon.
 */
class EnvWriter
{
    public static function update(array $values, ?string $path = null): void
    {
        $path ??= base_path('.env');

        $content = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.static::formatValue($value);

            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $content);
            } else {
                $content = rtrim($content)."\n".$line;
            }
        }

        file_put_contents($path, ltrim($content)."\n");
    }

    protected static function formatValue(mixed $value): string
    {
        $value = (string) $value;

        if ($value === '' || preg_match('/\s|#|"/', $value)) {
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }
}
