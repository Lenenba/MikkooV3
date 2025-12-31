<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\GeocodingService;
use App\Http\Requests\AddressOnboardingRequest;

class AddressOnboardingController extends Controller
{
    public function show(Request $request): RedirectResponse
    {
        return redirect()->route('onboarding.index', ['step' => 2]);
    }

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

    public function store(AddressOnboardingRequest $request): RedirectResponse
    {
        $address = $request->validated();
        $request->user()->address()->updateOrCreate([], $address);

        return to_route('onboarding.index', ['step' => 3]);
    }
}
