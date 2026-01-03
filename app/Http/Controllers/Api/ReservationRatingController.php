<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RatingRequest;
use App\Models\Reservation;
use App\Notifications\RatingReceivedNotification;
use App\Services\RatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReservationRatingController extends Controller
{
    public function store(RatingRequest $request, Reservation $reservation, RatingService $ratingService): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $rating = $ratingService->submitReservationRating(
            $reservation,
            $user,
            $request->integer('rating'),
            $request->input('comment')
        );

        if ($rating->wasRecentlyCreated && $rating->reviewee) {
            $rating->reviewee->notify(new RatingReceivedNotification($rating));
        }

        return response()->json([
            'rating' => [
                'id' => $rating->id,
                'reservation_id' => $rating->reservation_id,
                'reviewer_id' => $rating->reviewer_id,
                'reviewee_id' => $rating->reviewee_id,
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'created_at' => $rating->created_at?->toISOString(),
            ],
        ]);
    }
}
