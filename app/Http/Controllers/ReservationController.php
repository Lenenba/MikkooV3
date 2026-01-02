<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Inertia\Inertia;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ReservationRequest;
use App\Services\ReservationStatsService;
use App\Notifications\ReservationRequestedNotification;
use App\Support\Billing;

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
     * Display the details of a specific reservation.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $user = Auth::user();
        $reservation = Reservation::with([
            'parent',
            'babysitter',
            'babysitter.babysitterProfile',
            'babysitter.media',
            'babysitter.address',
            'services',
            'details'
        ])
            ->findOrFail($id);

        $taxRate = Billing::vatRateForCountry($reservation->babysitter?->address?->country);
        $currency = Billing::currencyForCountry($reservation->babysitter?->address?->country);

        $ratingsPayload = [
            'can_rate' => false,
            'mine' => null,
            'other' => null,
            'target_name' => null,
        ];

        if ($user) {
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
        }

        return Inertia::render('reservation/Show', [
            'reservation' => $reservation,
            'ratings' => $ratingsPayload,
            'tax_rate' => $taxRate,
            'currency' => $currency,
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
        $taxRate = Billing::vatRateForCountry($user->address?->country);
        $currency = Billing::currencyForCountry($user->address?->country);
        $myReservations = Reservation::where('parent_id', Auth::id())
            ->where('babysitter_id', $id)
            ->with(['parent', 'babysitter', 'services', 'details'])
            ->latest()
            ->get();

        return Inertia::render('reservation/Create', [
            'babysitter'     => $user,
            'myReservations' => $myReservations,
            'numero'    => Reservation::generateNextNumber($user->babysitterReservations->last()->number ?? null),
            'tax_rate' => $taxRate,
            'currency' => $currency,
        ]);
    }

    /**
     * Store a new reservation.
     *
     * @param  \App\Http\Requests\ReservationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ReservationRequest $request)
    {
        // Validate and extract request data
        $data = $request->validated();

        // Associate the reservation with the authenticated parent user
        $data['parent_id'] = Auth::id();

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
            return redirect()->back()->withErrors([
                'services' => __('reservations.errors.invalid_services'),
            ]);
        }

        $subtotal = (float) $serviceLines->sum('total');
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        // Create the reservation with server-calculated totals
        $reservation = Reservation::create([
            'parent_id' => $data['parent_id'],
            'babysitter_id' => $data['babysitter_id'],
            'total_amount' => $totalAmount,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($serviceLines->isNotEmpty()) {
            foreach ($serviceLines as $line) {
                $reservation->services()->attach($line['id'], [
                    'quantity' => $line['quantity'],
                    'total' => $line['total'],
                ]);
            }
        }

        // Create reservation details
        $scheduleType = $data['schedule_type'] ?? 'single';
        $reservation->details()->create([
            'date'                  => $data['start_date'],
            'start_time'            => $data['start_time'],
            'end_time'              => $data['end_time'],
            'status'                => 'pending', // Default status
            'schedule_type'         => $scheduleType,
            'recurrence_frequency'  => $scheduleType === 'recurring' ? ($data['recurrence_frequency'] ?? null) : null,
            'recurrence_interval'   => $scheduleType === 'recurring' ? ($data['recurrence_interval'] ?? null) : null,
            'recurrence_days'       => $scheduleType === 'recurring' ? ($data['recurrence_days'] ?? null) : null,
            'recurrence_end_date'   => $scheduleType === 'recurring' ? ($data['recurrence_end_date'] ?? null) : null,
        ]);

        $reservation->loadMissing([
            'parent.parentProfile',
            'babysitter.babysitterProfile',
            'details',
            'services',
        ]);

        if ($reservation->babysitter) {
            $reservation->babysitter->notify(new ReservationRequestedNotification($reservation));
        }

        // Redirect back with a success message
        return redirect()
            ->route('reservations.index')
            ->with('success', __('flash.reservation.created'));
    }
}
