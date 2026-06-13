<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, config('app.supported_locales', ['en', 'ar']), true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        return redirect()
            ->to($this->redirectTarget($request))
            ->withCookie(cookie()->forever('locale', $locale));
    }

    protected function redirectTarget(Request $request): string
    {
        $redirect = $request->query('redirect');

        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            return $redirect;
        }

        $previous = url()->previous();

        if (is_string($previous) && $previous !== '') {
            return $previous;
        }

        return route('home');
    }
}
