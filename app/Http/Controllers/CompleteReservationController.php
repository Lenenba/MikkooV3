<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Notifications\ReservationNotification;
use App\Services\InvoiceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompleteReservationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Mark a reservation as completed and generate an invoice.
     */
    public function __invoke(int $reservationId, InvoiceService $invoiceService)
    {
        $reservation = Reservation::with('details')->findOrFail($reservationId);

        if ($reservation->details?->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Reservation is not in a confirmed state.');
        }

        $this->authorize('complete', $reservation);

        $reservation->details()->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $reservation->parent?->notify(new ReservationNotification($reservation));
        $reservation->babysitter?->notify(new ReservationNotification($reservation));

        $invoiceService->createFromReservation($reservation);

        return redirect()->back()->with('success', 'Reservation terminee. Facture preparee.');
    }
}
