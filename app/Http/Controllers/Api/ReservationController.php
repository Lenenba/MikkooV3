<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReservationStoreRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Support\Billing;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class ReservationController extends Controller
{
    public function index(ReservationStatsService $statsService): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $reservations = Reservation::forUser($user)
            ->with([
                'parent',
                'parent.media',
                'babysitter',
                'babysitter.media',
                'babysitter.babysitterProfile',
                'services',
                'details',
            ])
            ->where(function ($query) {
                $query
                    ->whereNull('announcement_id')
                    ->orWhereHas('details', fn($details) => $details->where('status', '!=', 'pending'));
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => ReservationResource::collection($reservations),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $reservation = Reservation::forUser($user)->with([
            'parent',
            'babysitter',
            'babysitter.babysitterProfile',
            'babysitter.media',
            'babysitter.address',
            'services',
            'details',
        ])->findOrFail($id);

        $taxRate = Billing::vatRateForCountry($reservation->babysitter?->address?->country);
        $currency = Billing::currencyForCountry($reservation->babysitter?->address?->country);

        $ratingsPayload = [
            'can_rate' => false,
            'mine' => null,
            'other' => null,
            'target_name' => null,
        ];

        $canRate = $user->id === $reservation->parent_id || $user->id === $reservation->babysitter_id;

        if ($canRate) {
            $revieweeId = $user->id === $reservation->parent_id
                ? $reservation->babysitter_id
                : $reservation->parent_id;

            $ratingsPayload['can_rate'] = true;
            $ratingsPayload['mine'] = $reservation->ratings()
                ->where('reviewer_id', $user->id)
                ->first();

            $ratingsPayload['other'] = $revieweeId
                ? $reservation->ratings()->where('reviewer_id', $revieweeId)->first()
                : null;

            $profile = $reservation->babysitter?->babysitterProfile;
            $babysitterName = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''));
            $babysitterFallback = __('common.roles.babysitter');
            $parentFallback = __('common.roles.parent');
            $babysitterName = $babysitterName !== '' ? $babysitterName : ($reservation->babysitter?->name ?? $babysitterFallback);
            $parentName = $reservation->parent?->name ?? $parentFallback;

            $ratingsPayload['target_name'] = $user->id === $reservation->parent_id
                ? $babysitterName
                : $parentName;
        }

        return response()->json([
            'reservation' => new ReservationResource($reservation),
            'ratings' => $ratingsPayload,
            'tax_rate' => $taxRate,
            'currency' => $currency,
        ]);
    }

    public function store(ReservationStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->isParent()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email not verified.'], 403);
        }

        $data = $request->validated();
        $data['parent_id'] = $user->id;

        $babysitter = User::with('address')->findOrFail($data['babysitter_id']);
        $taxRate = Billing::vatRateForCountry($babysitter->address?->country);

        $servicePayload = collect($data['services'] ?? [])
            ->map(fn(array $service) => [
                'id' => (int) ($service['id'] ?? 0),
                'quantity' => max(1, (int) ($service['quantity'] ?? 1)),
            ])
            ->filter(fn(array $service) => $service['id'] > 0)
            ->values();

        $servicePrices = Service::query()
            ->where('user_id', $babysitter->id)
            ->whereIn('id', $servicePayload->pluck('id'))
            ->pluck('price', 'id')
            ->map(fn($price) => (float) $price);

        $serviceLines = $servicePayload
            ->map(function (array $service) use ($servicePrices): ?array {
                if (! $servicePrices->has($service['id'])) {
                    return null;
                }

                $unitPrice = (float) $servicePrices[$service['id']];
                $total = round($unitPrice * $service['quantity'], 2);

                return [
                    'id' => $service['id'],
                    'quantity' => $service['quantity'],
                    'total' => $total,
                ];
            })
            ->filter()
            ->values();

        if ($serviceLines->isEmpty()) {
            return response()->json([
                'message' => __('reservations.errors.invalid_services'),
                'errors' => ['services' => [__('reservations.errors.invalid_services')]],
            ], 422);
        }

        $subtotal = (float) $serviceLines->sum('total');
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $reservation = Reservation::create([
            'parent_id' => $data['parent_id'],
            'babysitter_id' => $data['babysitter_id'],
            'total_amount' => $totalAmount,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($serviceLines as $line) {
            $reservation->services()->attach($line['id'], [
                'quantity' => $line['quantity'],
                'total' => $line['total'],
            ]);
        }

        $scheduleType = $data['schedule_type'] ?? 'single';
        $reservation->details()->create([
            'date' => $data['start_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'status' => 'pending',
            'schedule_type' => $scheduleType,
            'recurrence_frequency' => $scheduleType === 'recurring' ? ($data['recurrence_frequency'] ?? null) : null,
            'recurrence_interval' => $scheduleType === 'recurring' ? ($data['recurrence_interval'] ?? null) : null,
            'recurrence_days' => $scheduleType === 'recurring' ? ($data['recurrence_days'] ?? null) : null,
            'recurrence_end_date' => $scheduleType === 'recurring' ? ($data['recurrence_end_date'] ?? null) : null,
        ]);

        $reservation->loadMissing([
            'parent',
            'babysitter',
            'services',
            'details',
        ]);

        return response()->json([
            'reservation' => new ReservationResource($reservation),
        ], 201);
    }
}
