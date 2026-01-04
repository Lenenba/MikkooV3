<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Notifications\ReservationNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StartReservationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mark a reservation as in progress.
     */
    public function __invoke(int $reservationId)
    {
        $reservation = Reservation::with('details')->findOrFail($reservationId);

        if ($reservation->details?->status !== 'confirmed') {
            return redirect()->back()->with('error', __('flash.reservation.not_confirmed'));
        }

        $this->authorize('start', $reservation);

        $reservation->details()->update([
            'status' => 'in_progress',
        ]);

        $reservation->load('details');

        $reservation->parent?->notify(new ReservationNotification($reservation));
        $reservation->babysitter?->notify(new ReservationNotification($reservation));

        return redirect()->back()->with('success', __('flash.reservation.in_progress'));
    }
}
