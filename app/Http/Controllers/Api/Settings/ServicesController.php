<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isBabysitter()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $services = Service::query()
            ->where('user_id', $user->id)
            ->withCount([
                'reservationServices as bookings_count' => function ($query) use ($user) {
                    $query->whereHas('reservation', function ($reservationQuery) use ($user) {
                        $reservationQuery->where('babysitter_id', $user->id);
                    });
                },
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'bookings_count' => (int) ($service->bookings_count ?? 0),
                ];
            })
            ->values();

        $topService = $services->sortByDesc('bookings_count')->first();
        $kpis = [
            'total_services' => $services->count(),
            'total_bookings' => $services->sum('bookings_count'),
            'top_service_name' => $topService['name'] ?? null,
            'top_service_count' => $topService['bookings_count'] ?? 0,
        ];

        $catalog = Service::query()
            ->whereNull('user_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'services' => $services,
            'catalog' => ServiceResource::collection($catalog),
            'kpis' => $kpis,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isBabysitter()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $service = Service::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
        ]);

        return response()->json([
            'service' => new ServiceResource($service),
        ], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isBabysitter()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $service->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $service->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
        ]);

        return response()->json([
            'service' => new ServiceResource($service),
        ]);
    }

    public function destroy(Request $request, Service $service): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isBabysitter()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $service->user_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $service->delete();

        return response()->json(null, 204);
    }
}
