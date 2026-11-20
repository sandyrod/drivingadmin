<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Interface language: the one picked with the ES / EN switch (session, remembered in a cookie),
 * otherwise the default language.
 */
class SetLocale
{
    public const COOKIE = 'nyds-lang';

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale') ?? $request->cookie(self::COOKIE);

        if (self::supported($locale)) {
            App::setLocale($locale);
            Carbon::setLocale($locale);
        }

        return $next($request);
    }

    public static function supported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, config('app.languages'));
    }

    /** Whether the user picked a language (otherwise pages may use the person's preferred one) */
    public static function chosen(Request $request): bool
    {
        return self::supported($request->session()->get('locale') ?? $request->cookie(self::COOKIE));
    }
}
