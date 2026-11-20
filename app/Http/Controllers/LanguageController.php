<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /** ES / EN switch: remembers the language and goes back to the same page */
    public function switch(Request $request, string $locale)
    {
        abort_unless(SetLocale::supported($locale), 404);

        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: route('dashboard'))
            ->withCookie(cookie()->forever(SetLocale::COOKIE, $locale));
    }
}
