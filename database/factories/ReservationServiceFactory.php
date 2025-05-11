<?php
// database/factories/ReservationServiceFactory.php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationServiceFactory extends Factory
{

    public function definition()
    {
        // Try to get a random existing service ID
        $existingServiceId = Service::query()
            ->inRandomOrder()
            ->value('id');

        return [
            // Link to a reservation header (new or provided)
            'reservation_id' => Reservation::factory(),
            // Use an existing service or create one if none exists
            'service_id'     => $existingServiceId
                ? $existingServiceId
                : Service::factory(),
            'quantity'       => $this->faker->numberBetween(1, 4),
            'price'          => $this->faker->randomFloat(2, 15, 100),
        ];
    }
}
