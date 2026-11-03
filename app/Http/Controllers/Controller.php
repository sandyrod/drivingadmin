<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Message safe to show to the user for an exception caught in a controller.
     * Validation-type errors (InvalidArgumentException) are shown as-is; anything
     * else is logged and replaced by a generic message so no SQL / internal
     * details leak to the browser.
     */
    protected function errorMessage(\Throwable $e, string $fallback = 'Ocurrió un error en el servidor. Intenta de nuevo.'): string
    {
        if ($e instanceof \InvalidArgumentException) {
            return $e->getMessage();
        }

        report($e);

        return $fallback;
    }
}
