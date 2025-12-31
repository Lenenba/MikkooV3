<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Services\RatingService;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Seed ratings for existing reservations.
     */
    public function run(): void
    {
        $reservations = Reservation::with(['parent', 'babysitter', 'details'])->get();

        if ($reservations->isEmpty()) {
            $this->command->warn('No reservations found. Skipping ratings seeding.');
            return;
        }

        $eligible = $reservations->filter(fn($reservation) => $reservation->details?->status === 'confirmed');
        if ($eligible->isEmpty()) {
            $eligible = $reservations;
        }

        $ratingService = app(RatingService::class);
        $created = 0;
        $parentComments = [
            'Great communication and very caring.',
            'On time and the kids loved the activities.',
            'Clean, organized, and super attentive.',
            'Felt safe and confident during the whole stay.',
            'Highly recommended, will book again.',
        ];

        foreach ($eligible as $reservation) {
            $parent = $reservation->parent;
            $babysitter = $reservation->babysitter;

            if (!$parent || !$babysitter) {
                continue;
            }

            $ratingService->submitReservationRating(
                $reservation,
                $parent,
                fake()->numberBetween(3, 5),
                fake()->randomElement($parentComments)
            );
            $created++;

            if (fake()->boolean(70)) {
                $ratingService->submitReservationRating(
                    $reservation,
                    $babysitter,
                    fake()->numberBetween(3, 5),
                    fake()->boolean(35) ? fake()->sentence() : null
                );
                $created++;
            }
        }

        $this->command->info("Ratings seeding completed: {$created} entries created.");
    }
}
