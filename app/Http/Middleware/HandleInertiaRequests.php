<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Http\Request;
use Illuminate\Foundation\Inspiring;
use App\Http\Controllers\Traits\UtilsPhotoConverter;
use App\Support\Translations;

class HandleInertiaRequests extends Middleware
{
    use UtilsPhotoConverter;
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        $user = $request->user();
        $role = null;
        if ($user) {
            if ($user->isAdmin()) {
                $role = 'SuperAdmin';
            } elseif ($user->isBabysitter()) {
                $role = 'Babysitter';
            } elseif ($user->isParent()) {
                $role = 'Parent';
            }
        }
        $defaultAvatar = $user
            ? ($user->isBabysitter() ? 'bbsiter.png' : 'parent.png')
            : null;
        $profilePhotoPath = $user?->media()->isProfilePicture()->first()?->file_path
            ?? $defaultAvatar;

        $locale = app()->getLocale();
        $availableLocales = config('app.available_locales', [$locale]);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'supportEmail' => config('mail.from.address'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'locale' => $locale,
            'fallbackLocale' => config('app.fallback_locale'),
            'availableLocales' => $availableLocales,
            'translations' => Translations::forLocale($locale),
            'auth' => [
                'user' => $request->user(),
                'profilPicture' => $this->convertToWebp($profilePhotoPath),
                'role' => $role,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error')
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
