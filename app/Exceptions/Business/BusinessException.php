<?php

namespace App\Exceptions\Business;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class BusinessException extends Exception
{
    protected int $httpStatus = 422;

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Laravel appelle automatiquement render() sur toute exception qui
     * l'expose — pas besoin d'enregistrer un cas par exception dans le
     * handler global.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->httpStatus);
    }

    protected static function formatFcfa(int $centimes): string
    {
        return number_format(intdiv($centimes, 100), 0, ',', ' ').' FCFA';
    }
}
