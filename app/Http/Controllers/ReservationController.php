<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{

    /**
     * Affiche la liste des réservations.
     *
     */
    public function index()
    {
        $user = Auth::user();

        $reservations = Reservation::forUser($user)
            ->with(['parent', 'babysitter', 'services', 'details', 'referenceNumero'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $BabysitterreservationData = [];

        foreach ($reservations as $reservation) {
            $BabysitterreservationData[] = [
                'id' => $reservation->id,
                'ref' => $reservation->referenceNumero->chaine_reference,
                'parent' => $reservation->parent,
                'babysitter' => $reservation->babysitter,
                'services' => $reservation->services,
                'details' => $reservation->details,
                'total_amount' => $reservation->total_amount,
                'notes' => $reservation->notes,
                // 'date' => $reservation->details->date->format('d/m/Y'),
                // 'end_time' => $reservation->details->end_time->format('H:i'),
                // 'start_time' => $reservation->details->start_time->format('H:i'),
                // 'status' => $reservation->details->status,
            ];
        }

        return Inertia::render('reservation/Index', [
            'reservations' => $BabysitterreservationData,
        ]);
    }
}
