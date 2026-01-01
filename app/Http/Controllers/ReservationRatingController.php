<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingRequest;
use App\Models\Reservation;
use App\Services\RatingService;
use App\Notifications\RatingReceivedNotification;
use Illuminate\Support\Facades\Auth;

class ReservationRatingController extends Controller
{
    public function store(RatingRequest $request, Reservation $reservation, RatingService $ratingService)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
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

        return back()->with('success', __('Rating saved.'));
    }
}
