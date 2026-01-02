<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BabysitterServicesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isBabysitter()) {
            abort(403);
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
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                ];
            })
            ->values();

        return Inertia::render('settings/Services', [
            'services' => $services,
            'catalog' => $catalog,
            'kpis' => $kpis,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isBabysitter()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        Service::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
        ]);

        return back()->with('success', __('flash.service.added'));
    }

    public function update(Request $request, Service $service)
    {
        $user = $request->user();
        if (!$user || !$user->isBabysitter()) {
            abort(403);
        }

        if ((int) $service->user_id !== (int) $user->id) {
            abort(403);
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

        return back()->with('success', __('flash.service.updated'));
    }

    public function destroy(Request $request, Service $service)
    {
        $user = $request->user();
        if (!$user || !$user->isBabysitter()) {
            abort(403);
        }

        if ((int) $service->user_id !== (int) $user->id) {
            abort(403);
        }

        $service->delete();

        return back()->with('success', __('flash.service.deleted'));
    }
}
