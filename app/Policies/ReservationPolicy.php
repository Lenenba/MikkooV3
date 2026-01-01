<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can accept the reservation.
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Reservation  $reservation
     * @return bool
     */
    public function accept(User $user, Reservation $reservation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $reservation->babysitter_id;
    }

    /**
     * Determine whether the user can cancel the reservation.
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Reservation  $reservation
     * @return bool
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $reservation->babysitter_id;
    }

    /**
     * Determine whether the user can mark the reservation as completed.
     */
    public function complete(User $user, Reservation $reservation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $reservation->babysitter_id;
    }

    /**
     * Determine whether the user can view the reservation.
     *
     * @param  \App\Models\User         $user
     * @param  \App\Models\Reservation  $reservation
     * @return bool
     */
    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $reservation->babysitter_id || $user->id === $reservation->user_id;
    }
}
