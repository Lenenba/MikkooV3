<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\ReservationMediaRequest;
use App\Models\User;
use App\Notifications\ReservationMediaRequestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationMediaRequestController extends Controller
{
    public function index(Reservation $reservation): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $this->canAccess($user, $reservation)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $requests = $reservation->mediaRequests()
            ->with(['requester', 'fulfiller'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(ReservationMediaRequest $request) => $this->formatRequest($request));

        return response()->json([
            'media_requests' => $requests,
        ]);
    }

    public function store(Request $request, Reservation $reservation): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->isParent() || (int) $reservation->parent_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $reservation->loadMissing('details');
        $status = $reservation->details?->status;
        if ($status !== 'in_progress') {
            return response()->json([
                'message' => __('reservations.media_requests.errors.not_allowed'),
            ], 422);
        }

        $existingPending = $reservation->mediaRequests()
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return response()->json([
                'message' => __('reservations.media_requests.errors.pending_exists'),
            ], 422);
        }

        $payload = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $mediaRequest = $reservation->mediaRequests()->create([
            'requester_id' => $user->id,
            'status' => 'pending',
            'note' => $payload['note'] ?? null,
        ]);

        $reservation->babysitter?->notify(
            new ReservationMediaRequestNotification($mediaRequest, 'requested')
        );

        return response()->json([
            'media_request' => $this->formatRequest($mediaRequest->loadMissing(['requester', 'fulfiller'])),
        ], 201);
    }

    public function cancel(Reservation $reservation, ReservationMediaRequest $mediaRequest): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($mediaRequest->reservation_id !== $reservation->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if (! $user->isParent() || (int) $mediaRequest->requester_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($mediaRequest->status !== 'pending') {
            return response()->json(['message' => 'Invalid status.'], 422);
        }

        $mediaRequest->update([
            'status' => 'canceled',
        ]);

        return response()->json([
            'media_request' => $this->formatRequest($mediaRequest->fresh(['requester', 'fulfiller'])),
        ]);
    }

    protected function canAccess(User $user, Reservation $reservation): bool
    {
        if ($user->isParent()) {
            return (int) $reservation->parent_id === (int) $user->id;
        }

        if ($user->isBabysitter()) {
            return (int) $reservation->babysitter_id === (int) $user->id;
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatRequest(ReservationMediaRequest $request): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status,
            'note' => $request->note,
            'created_at' => $request->created_at?->toISOString(),
            'fulfilled_at' => $request->fulfilled_at?->toISOString(),
            'requester' => $request->requester ? [
                'id' => $request->requester->id,
                'name' => $request->requester->name,
            ] : null,
            'fulfiller' => $request->fulfiller ? [
                'id' => $request->fulfiller->id,
                'name' => $request->fulfiller->name,
            ] : null,
        ];
    }
}
