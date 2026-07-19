<?php

namespace App\Support;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\Output\QRGdImagePNG;

class QrCodeGenerator
{
    public static function svg(string $data): string
    {
        $options = new QROptions;
        $options->outputInterface = QRMarkupSVG::class;
        $options->outputBase64    = false;

        return (new QRCode($options))->render($data);
    }

    public static function pngBase64(string $data): string
    {
        $options = new QROptions;
        $options->outputInterface = QRGdImagePNG::class;
        $options->outputBase64    = true;
        $options->scale           = 5;

        return (new QRCode($options))->render($data);
    }
}
