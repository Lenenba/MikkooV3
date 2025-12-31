<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class RatingService
{
    /**
     * Create or update a rating for a reservation.
     */
    public function submitReservationRating(
        Reservation $reservation,
        User $reviewer,
        int $rating,
        ?string $comment = null
    ): Rating {
        $revieweeId = $this->resolveRevieweeId($reservation, $reviewer->id);

        if (!$revieweeId) {
            throw new AuthorizationException('Reviewer is not part of this reservation.');
        }

        return Rating::updateOrCreate(
            [
                'reservation_id' => $reservation->id,
                'reviewer_id' => $reviewer->id,
            ],
            [
                'reviewee_id' => $revieweeId,
                'rating' => $rating,
                'comment' => $comment,
            ]
        );
    }

    protected function resolveRevieweeId(Reservation $reservation, int $userId): ?int
    {
        if ((int) $reservation->parent_id === $userId) {
            return $reservation->babysitter_id ? (int) $reservation->babysitter_id : null;
        }

        if ((int) $reservation->babysitter_id === $userId) {
            return $reservation->parent_id ? (int) $reservation->parent_id : null;
        }

        return null;
    }
}
