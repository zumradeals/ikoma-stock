<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Redimensionne/compresse une image avant stockage. Sert le même public
 * visé par le reste de l'app (secteur informel, connexions souvent
 * faibles) : accepter une vraie photo de téléphone (plusieurs Mo) sans
 * pour autant garder ce poids sur le serveur ni le renvoyer tel quel à
 * chaque affichage.
 */
class ImageOptimizer
{
    public static function storeCompressed(UploadedFile $file, string $directory, string $disk = 'public', int $maxWidth = 1000, int $quality = 75): string
    {
        $size = @getimagesize($file->getRealPath());

        if (! $size) {
            return $file->store($directory, $disk);
        }

        [$width, $height] = $size;

        $image = match ($file->getMimeType()) {
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
            default => @imagecreatefromjpeg($file->getRealPath()),
        };

        if (! $image) {
            return $file->store($directory, $disk);
        }

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        // Écrit dans un vrai fichier temporaire plutôt que d'utiliser
        // ob_start()/imagejpeg(null) : dans le contexte d'une vraie requête
        // HTTP, un buffer de sortie déjà actif (output_buffering du SAPI)
        // peut faire fuiter/tronquer les données capturées, et
        // Storage::put() n'aurait alors écrit qu'un fichier corrompu ou
        // rien du tout (les disques `local`/`public` de ce projet ont
        // `throw => false` : un échec d'écriture est silencieux, sans
        // exception ni log).
        $tmpPath = tempnam(sys_get_temp_dir(), 'img').'.jpg';
        imagejpeg($image, $tmpPath, $quality);
        imagedestroy($image);

        $contents = file_get_contents($tmpPath);
        @unlink($tmpPath);

        $path = trim($directory, '/').'/'.Str::random(40).'.jpg';
        $written = Storage::disk($disk)->put($path, $contents);

        if (! $written) {
            return $file->store($directory, $disk);
        }

        return $path;
    }
}
