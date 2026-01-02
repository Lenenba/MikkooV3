<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchBabysitterRequest;
use App\Models\User;
use App\Services\UnsplashService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke(SearchBabysitterRequest $request, UnsplashService $unsplash)
    {
        $filters = $request->validated();
        $hasSearch = collect($filters)
            ->filter(fn($value) => $value !== null && $value !== '')
            ->isNotEmpty();

        $babysitters = collect();
        if ($hasSearch) {
            $babysitters = User::babysitters()
                ->with(['address', 'babysitterProfile', 'media'])
                ->withRatingSummary()
                ->filter($filters)
                ->orderByDesc('rating_avg')
                ->orderByDesc('rating_count')
                ->limit(8)
                ->get();
        }

        $media = Cache::remember('welcome.unsplash', now()->addHours(6), function () use ($unsplash) {
            $fallback = [
                'hero' => [
                    ['url' => '/enfant1.png', 'alt' => 'Happy parent and child'],
                    ['url' => '/enfant3.png', 'alt' => 'Smiling baby'],
                    ['url' => '/enfant4.png', 'alt' => 'Happy family'],
                ],
                'area' => [
                    ['url' => '/parent.png', 'alt' => 'Parent portrait'],
                    ['url' => '/enfant5.png', 'alt' => 'Child portrait'],
                    ['url' => '/bbsiter.png', 'alt' => 'Babysitter portrait'],
                ],
                'video' => [
                    ['url' => '/enfant6.png', 'alt' => 'Family moment'],
                ],
                'testimonials' => [
                    ['url' => '/parent.png', 'alt' => 'Happy parent'],
                    ['url' => '/enfant1.png', 'alt' => 'Happy child'],
                    ['url' => '/enfant3.png', 'alt' => 'Happy family'],
                    ['url' => '/bbsiter.png', 'alt' => 'Babysitter'],
                ],
            ];

            $hero = $unsplash->random('happy family baby', 3, ['orientation' => 'landscape']);
            $area = $unsplash->random('parent child smiling', 3, ['orientation' => 'squarish']);
            $video = $unsplash->random('family playing', 1, ['orientation' => 'landscape']);
            $testimonials = $unsplash->random('parent portrait', 4, ['orientation' => 'squarish']);

            return [
                'hero' => $hero ?: $fallback['hero'],
                'area' => $area ?: $fallback['area'],
                'video' => $video ?: $fallback['video'],
                'testimonials' => $testimonials ?: $fallback['testimonials'],
            ];
        });

        return Inertia::render('Welcome', [
            'filters' => $filters,
            'babysitters' => $babysitters,
            'search_active' => $hasSearch,
            'media' => $media,
        ]);
    }
}
