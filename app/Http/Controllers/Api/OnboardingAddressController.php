<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressOnboardingRequest;
use App\Http\Resources\AddressResource;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingAddressController extends Controller
{
    public function search(Request $request, GeocodingService $geocoder): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        $results = $geocoder->search($validated['query']);

        return response()->json([
            'results' => $results,
        ]);
    }

    public function store(AddressOnboardingRequest $request): JsonResponse
    {
        $address = $request->validated();
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->address()->updateOrCreate([], $address);

        $user->loadMissing('address');

        return response()->json([
            'address' => AddressResource::make($user->address),
            'next_step' => 3,
        ]);
    }
}
