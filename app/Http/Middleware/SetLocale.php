<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Set the application locale for the current request.
     */
    public function handle(Request $request, Closure $next)
    {
        $available = config('app.available_locales', []);
        $locale = null;

        $requested = $request->query('lang');
        if ($requested && in_array($requested, $available, true)) {
            $locale = $requested;
            $request->session()->put('locale', $locale);
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } elseif ($request->user() && isset($request->user()->locale)) {
            $userLocale = $request->user()->locale;
            if (in_array($userLocale, $available, true)) {
                $locale = $userLocale;
            }
        } else {
            $locale = $this->resolveFromHeader($request->header('Accept-Language'), $available);
        }

        $locale = $locale ?: config('app.locale');

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    private function resolveFromHeader(?string $header, array $available): ?string
    {
        if (! $header) {
            return null;
        }

        $segments = explode(',', $header);

        foreach ($segments as $segment) {
            $lang = strtolower(trim(explode(';', $segment)[0]));
            $lang = substr($lang, 0, 2);
            if (in_array($lang, $available, true)) {
                return $lang;
            }
        }

        return null;
    }
}
