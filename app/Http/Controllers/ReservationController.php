<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Reservation;
use App\Http\Requests\ReservationRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\ReservationStatsService;
use App\Models\Traits\GeneratesSequentialNumber;

class ReservationController extends Controller
{

    /**
     * Display the list of reservations and dynamic stats.
     */
    public function index(ReservationStatsService $statsService)
    {
        $user  = Auth::user();
        $stats = $statsService->getAllStats();
        $babysitters = User::Babysitters()
            ->with(['address', 'babysitterProfile', 'media'])
            ->MostRecent()
            ->get()
            ->map(function (User $babysitter) {
                // Safely fetch the related profile (could be null)
                $profile = optional($babysitter->babysitterProfile);

                // Retrieve the first media item marked as profile picture
                $profileMedia = $babysitter
                    ->media()                           // Relation query, not the loaded collection
                    ->isProfilePicture()               // Scope on the Media model
                    ->first();

                return [
                    'id'              => $babysitter->id,
                    'name'            => trim($profile->first_name . ' ' . $profile->last_name),
                    'email'           => $babysitter->email,
                    'profile_picture' => $profileMedia?->file_path,
                    'price_per_hour'  => $profile->price_per_hour ?? 0,
                ];
            });

        $reservations = Reservation::forUser($user)
            ->with(['parent', 'babysitter', 'services', 'details'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn($r) => [
                'id'                  => $r->id,
                'ref'                 => $r->number,
                'status'              => $r->details->status,
                'created_at'          => $r->created_at->diffForHumans(),
                'parent'              => $r->parent,
                'babysitter'          => $r->babysitter,
                'services'            => $r->services,
                'details'             => $r->details,
                'total_amount'        => $r->total_amount,
                'notes'               => $r->notes,
            ]);

        return Inertia::render('reservation/Index', [
            'reservations' => $reservations,
            'stats'        => $stats,
            'babysitters'  => $babysitters,
        ]);
    }

    /**
     * Show the form for booking a babysitter.
     */
    public function create(int $id)
    {

        $user = User::Babysitters()
            ->with(['address', 'babysitterProfile', 'media'])
            ->where('id', $id)
            ->firstOrFail();
        $myReservations = Reservation::where('parent_id', Auth::id())
            ->where('babysitter_id', $id)
            ->with(['parent', 'babysitter', 'services', 'details'])
            ->latest()
            ->get();

        return Inertia::render('reservation/Create', [
            'babysitter'     => $user,
            'myReservations' => $myReservations,
            'numero'    => Reservation::generateNextNumber($user->babysitterReservations->last()->number ?? null),
        ]);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(ReservationRequest $request)
    {
        $validated = $request->validated();

        $reservation = Reservation::create([
            'parent_id'     => Auth::id(),
            'babysitter_id' => $validated['babysitter_id'],
            'notes'         => $validated['notes'] ?? null,
        ]);

        $reservation->details()->create([
            'date'       => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time'   => $validated['end_time'],
        ]);

        $total = 0;
        foreach ($validated['services'] as $service) {
            $reservation->reservationServices()->create([
                'service_id' => $service['service_id'],
                'quantity'   => $service['quantity'],
                'price'      => $service['price'],
            ]);

            $total += $service['quantity'] * $service['price'];
        }

        $reservation->update(['total_amount' => $total]);

        return redirect()->route('reservations.index')->with('success', 'Reservation created successfully.');
    }
}
