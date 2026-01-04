<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ReservationMediaRequest;
use App\Notifications\ReservationMediaRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationMediaRequestController extends Controller
{
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->back();
        }

        if (! $user->isParent() || (int) $reservation->parent_id !== (int) $user->id) {
            abort(403);
        }

        $reservation->loadMissing('details');
        $status = $reservation->details?->status;
        if ($status !== 'in_progress') {
            return redirect()->back()->withErrors([
                'media_request' => __('reservations.media_requests.errors.not_allowed'),
            ]);
        }

        $existingPending = $reservation->mediaRequests()
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()->back()->withErrors([
                'media_request' => __('reservations.media_requests.errors.pending_exists'),
            ]);
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

        return redirect()->back()->with('success', __('flash.reservation.media_request_sent'));
    }

    public function cancel(Reservation $reservation, ReservationMediaRequest $mediaRequest): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->back();
        }

        if ($mediaRequest->reservation_id !== $reservation->id) {
            abort(404);
        }

        if (! $user->isParent() || (int) $mediaRequest->requester_id !== (int) $user->id) {
            abort(403);
        }

        if ($mediaRequest->status !== 'pending') {
            return redirect()->back();
        }

        $mediaRequest->update([
            'status' => 'canceled',
        ]);

        return redirect()->back()->with('success', __('flash.reservation.media_request_canceled'));
    }
}
