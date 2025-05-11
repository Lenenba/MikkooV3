<?php
// database/factories/ReservationDetailFactory.php

namespace Database\Factories;

use App\Models\ReservationDetail;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationDetailFactory extends Factory
{
    public function definition()
    {
        // Random date within next month
        $date      = $this->faker->dateTimeBetween('now', '+1 month');
        $startTime = (clone $date)->modify('+' . $this->faker->numberBetween(1, 3) . ' hours');
        $endTime   = (clone $startTime)->modify('+' . $this->faker->numberBetween(1, 3) . ' hours');

        return [
            // Link to reservation header
            'reservation_id' => Reservation::factory(),
            // Date of the slot
            'date'           => $date->format('Y-m-d'),
            // Start and end times
            'start_time'     => $startTime->format('H:i:s'),
            'end_time'       => $endTime->format('H:i:s'),
            // Status of this detail line
            'status'         => $this->faker->randomElement(['pending', 'confirmed', 'canceled']),
        ];
    }
}
