<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use App\Http\Requests\SearchBabysitterRequest;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SearchBabysitterController extends Controller
{
    use AuthorizesRequests;
    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\SearchBabysitterRequest  $request
     * @return \Inertia\Response
     */
    public function __invoke(SearchBabysitterRequest $request)
    {
        $filters = $request->validated();
        $babysittersQuery = User::Babysitters()
            ->leftJoin('babysitter_profiles', 'babysitter_profiles.user_id', '=', 'users.id')
            ->leftJoin('addresses as user_addresses', function ($join) {
                $join->on('user_addresses.addressable_id', '=', 'users.id')
                    ->where('user_addresses.addressable_type', '=', User::class)
                    ->whereNull('user_addresses.deleted_at');
            })
            ->select('users.*')
            ->with([
                'address',
                'babysitterProfile',
                'media',
                'receivedRatings' => function ($query) {
                    $query
                        ->whereNotNull('comment')
                        ->latest()
                        ->take(3)
                        ->with('reviewer:id,name');
                },
            ])
            ->withRatingSummary()
            ->Filter($filters);

        $parent = $request->user();
        $parentAddress = $parent && $parent->isParent()
            ? $parent->address
            : null;

        $parentLat = $parentAddress?->latitude;
        $parentLng = $parentAddress?->longitude;
        $parentCity = $parentAddress?->city;
        $parentCountry = $parentAddress?->country;

        $sort = $filters['sort'] ?? null;
        if ($sort === 'distance' && $parent && $parent->isParent()) {
            $this->applyLocationSort(
                $babysittersQuery,
                $parentLat,
                $parentLng,
                $parentCity,
                $parentCountry
            );
        }

        if (! $sort && $parent && $parent->isParent()) {
            $this->applyLocationSort(
                $babysittersQuery,
                $parentLat,
                $parentLng,
                $parentCity,
                $parentCountry
            );
        }

        switch ($sort) {
            case 'rating':
                $babysittersQuery->orderByDesc('rating_avg')
                    ->orderByDesc('rating_count');
                break;
            case 'price_low':
                $babysittersQuery->orderBy('babysitter_profiles.price_per_hour')
                    ->orderByDesc('rating_avg')
                    ->orderByDesc('rating_count');
                break;
            case 'price_high':
                $babysittersQuery->orderByDesc('babysitter_profiles.price_per_hour')
                    ->orderByDesc('rating_avg')
                    ->orderByDesc('rating_count');
                break;
            case 'recent':
                $babysittersQuery->orderByDesc('users.created_at');
                break;
            case 'distance':
                $babysittersQuery->orderByDesc('rating_avg')
                    ->orderByDesc('rating_count');
                break;
            default:
                $babysittersQuery->orderByDesc('rating_avg')
                    ->orderByDesc('rating_count');
                break;
        }

        if ($sort !== 'recent') {
            $babysittersQuery->orderByDesc('users.created_at');
        }

        $babysitters = $babysittersQuery
            ->simplePaginate(12)
            ->withQueryString();

        $serviceOptions = Service::query()
            ->whereNotNull('user_id')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return Inertia::render('search/Index', [
            'babysitters' => $babysitters,
            'filters' => $filters,
            'serviceOptions' => $serviceOptions,
        ]);
    }

    private function applyLocationSort(
        Builder $query,
        ?float $latitude,
        ?float $longitude,
        ?string $city,
        ?string $country
    ): bool {
        $hasLocation = false;

        if ($latitude !== null && $longitude !== null) {
            $latExpr = 'user_addresses.latitude';
            $lngExpr = 'user_addresses.longitude';
            $distanceSql = "(6371 * acos(cos(radians(?)) * cos(radians($latExpr)) * cos(radians($lngExpr) - radians(?)) + sin(radians(?)) * sin(radians($latExpr))))";
            $query->selectRaw($distanceSql . ' as distance_km', [$latitude, $longitude, $latitude])
                ->orderByRaw('distance_km is null asc')
                ->orderBy('distance_km');
            $hasLocation = true;
        } else {
            if ($city) {
                $query->orderByRaw(
                    'user_addresses.city = ? desc',
                    [$city]
                );
                $hasLocation = true;
            }
            if ($country) {
                $query->orderByRaw(
                    'user_addresses.country = ? desc',
                    [$country]
                );
                $hasLocation = true;
            }
        }

        return $hasLocation;
    }
}
