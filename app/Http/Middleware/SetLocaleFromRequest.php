<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->isApiRequest($request)
            ? $this->resolveForApi($request)
            : $this->resolveForWeb($request);

        App::setLocale($locale);

        return $next($request);
    }

    protected function isApiRequest(Request $request): bool
    {
        // Blade admin lives under /admin (web middleware), not the JSON API.
        if ($request->is('admin', 'admin/*', 'locale', 'locale/*')) {
            return false;
        }

        return $request->is('api/*');
    }

    protected function resolveForApi(Request $request): string
    {
        if (Session::has('locale')) {
            return $this->normalizeSupported((string) Session::get('locale'));
        }

        $locale = $this->resolveFromRequest($request);

        if ($locale !== '') {
            return $this->normalizeSupported($locale);
        }

        $cookieLocale = $request->cookie('locale');

        if (is_string($cookieLocale) && $cookieLocale !== '') {
            return $this->normalizeSupported($cookieLocale);
        }

        return $this->normalizeSupported('');
    }

    protected function resolveForWeb(Request $request): string
    {
        if (Session::has('locale')) {
            return $this->normalizeSupported((string) Session::get('locale'));
        }

        $cookieLocale = $request->cookie('locale');

        if (is_string($cookieLocale) && $cookieLocale !== '') {
            return $this->normalizeSupported($cookieLocale);
        }

        return $this->normalizeSupported($this->resolveFromRequest($request));
    }

    protected function resolveFromRequest(Request $request): string
    {
        if ($request->filled('locale')) {
            return $this->normalizeLocale((string) $request->input('locale'));
        }

        if ($request->filled('lang')) {
            return $this->normalizeLocale((string) $request->input('lang'));
        }

        $header = $request->header('Accept-Language');

        if ($header === null || $header === '') {
            return '';
        }

        $supported = config('app.supported_locales', ['en', 'ar']);

        foreach ($this->parseAcceptLanguage($header) as $candidate) {
            if (in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        return '';
    }

    protected function normalizeSupported(string $locale): string
    {
        $supported = config('app.supported_locales', ['en', 'ar']);

        if (in_array($locale, $supported, true)) {
            return $locale;
        }

        return (string) config('app.locale', 'en');
    }

    /**
     * @return list<string>
     */
    protected function parseAcceptLanguage(string $header): array
    {
        $locales = [];

        foreach (explode(',', $header) as $part) {
            $tag = trim(explode(';', $part)[0]);

            if ($tag === '') {
                continue;
            }

            $locales[] = $this->normalizeLocale($tag);
        }

        return array_values(array_unique($locales));
    }

    protected function normalizeLocale(string $locale): string
    {
        $locale = strtolower(str_replace('_', '-', $locale));

        if (str_contains($locale, '-')) {
            return explode('-', $locale)[0];
        }

        return $locale;
    }
}
