<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Notifications\ReservationNotification;
use App\Services\InvoiceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationActionController extends Controller
{
    use AuthorizesRequests;

    public function accept(Request $request, int $reservationId): JsonResponse
    {
        $reservation = Reservation::with('details')->findOrFail($reservationId);

        if ($reservation->details?->status !== 'pending') {
            return response()->json(['message' => __('flash.reservation.not_pending')], 422);
        }

        $this->authorize('accept', $reservation);

        $reservation->details()->update([
            'status' => 'confirmed',
        ]);

        $reservation->loadMissing(['details', 'parent', 'babysitter', 'services']);

        $reservation->parent?->notify(new ReservationNotification($reservation));
        $reservation->babysitter?->notify(new ReservationNotification($reservation));

        return response()->json([
            'message' => __('flash.reservation.confirmed'),
            'reservation' => new ReservationResource($reservation),
        ]);
    }

    public function cancel(Request $request, int $reservationId): JsonResponse
    {
        $reservation = Reservation::with('details')->findOrFail($reservationId);

        if ($reservation->details?->status === 'canceled') {
            return response()->json(['message' => __('flash.reservation.already_canceled')], 422);
        }

        $this->authorize('cancel', $reservation);

        $reservation->details()->update([
            'status' => 'canceled',
        ]);

        $reservation->loadMissing(['details', 'parent', 'babysitter', 'services']);

        $reservation->parent?->notify(new ReservationNotification($reservation));

        return response()->json([
            'message' => __('flash.reservation.canceled'),
            'reservation' => new ReservationResource($reservation),
        ]);
    }

    public function complete(Request $request, int $reservationId, InvoiceService $invoiceService): JsonResponse
    {
        $reservation = Reservation::with('details')->findOrFail($reservationId);

        if ($reservation->details?->status !== 'confirmed') {
            return response()->json(['message' => __('flash.reservation.not_confirmed')], 422);
        }

        $this->authorize('complete', $reservation);

        $reservation->details()->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $reservation->load('details');

        $reservation->parent?->notify(new ReservationNotification($reservation));
        $reservation->babysitter?->notify(new ReservationNotification($reservation));

        $invoiceService->createFromReservation($reservation);

        $reservation->loadMissing(['parent', 'babysitter', 'services']);

        return response()->json([
            'message' => __('flash.reservation.completed'),
            'reservation' => new ReservationResource($reservation),
        ]);
    }
}
