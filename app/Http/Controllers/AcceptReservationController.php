<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Notifications\ReservationNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AcceptReservationController extends Controller
{
    use AuthorizesRequests;


    // protected WorkSessionGeneratorService $workGenerator;

    // public function __construct(WorkSessionGeneratorService $workGenerator)
    // {
    //     $this->workGenerator = $workGenerator;
    // }

    /**
     * Accept a reservation.
     *
     * @param  int  $reservationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(int $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        if ($reservation->details->status !== 'pending') {
            return redirect()->back()->with('error', __('flash.reservation.not_pending'));
        }
        // Authorize the action using the ReservationPolicy
        $this->authorize('accept', $reservation);

        // English comment: Update the related detail record
        $reservation->details()->update([
            'status' => 'confirmed',
        ]);

        // English comment: Notify the parent user
        $reservation->parent->notify(
            new ReservationNotification($reservation)
        );

        // English comment: notify the parent via the Notification system
        $reservation->babysitter->notify(
            new ReservationNotification($reservation)
        );

        // Générer les sessions de travail
        // $this->workGenerator->generate($reservation);

        // Redirect back with a success message
        return redirect()->route('reservations.index', $reservation->id)->with('success', __('flash.reservation.confirmed'));
    }
}
