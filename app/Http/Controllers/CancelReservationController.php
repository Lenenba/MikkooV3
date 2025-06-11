<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Notifications\ReservationNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CancelReservationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Cancel a reservation.
     *
     * @param  int  $reservationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(int $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        if ($reservation->details->status === 'canceled') {
            return redirect()->back()->with('error', 'This Reservation is already cancelled.');
        }

        // Authorize the action using the ReservationPolicycanceled
        $this->authorize('cancel', $reservation);

        // English comment: Update the related detail record
        $reservation->details()->update([
            'status' => 'canceled',
        ]);

        // English comment: Notify the parent user
        $reservation->parent->notify(
            new ReservationNotification($reservation)
        );

        // Redirect back with a success message
        return back()->with('success', 'Reservation canceled successfully!');
    }
}
