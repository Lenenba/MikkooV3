<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Services\RatingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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
        $babysitterComments = [
            'Smooth booking with clear instructions.',
            'Kids were lovely, great experience.',
            'Parents were responsive and helpful.',
            'Everything went well and on time.',
        ];
        $randomBool = static fn(int $percent) => mt_rand(1, 100) <= $percent;

        foreach ($eligible as $reservation) {
            $parent = $reservation->parent;
            $babysitter = $reservation->babysitter;

            if (!$parent || !$babysitter) {
                continue;
            }

            $ratingService->submitReservationRating(
                $reservation,
                $parent,
                mt_rand(3, 5),
                Arr::random($parentComments)
            );
            $created++;

            if ($randomBool(70)) {
                $ratingService->submitReservationRating(
                    $reservation,
                    $babysitter,
                    mt_rand(3, 5),
                    $randomBool(35) ? Arr::random($babysitterComments) : null
                );
                $created++;
            }
        }

        $this->command->info("Ratings seeding completed: {$created} entries created.");
    }
}
