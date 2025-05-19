<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Service;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\ReservationService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id'     => User::factory(),
            'babysitter_id' => User::factory(),
            'total_amount'  => 0, // Initialisé à 0, sera recalculé
            'notes'         => $this->faker->sentence(),
        ];
    }

    /**
     * Configure the factory to create exactly one detail and related services.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Reservation $reservation) {
            // Create exactly one detail for this reservation
            ReservationDetail::factory()
                ->create([
                    'reservation_id' => $reservation->id,
                ]);

            // Ensure there are services to attach
            $serviceIds = Service::pluck('id')->toArray();
            if (empty($serviceIds)) {
                Service::factory()->count(mt_rand(2, 5))->create();
                $serviceIds = Service::pluck('id')->toArray();
            }

            // Attach between 1 and 3 services and calculate the total
            $calculatedTotal = 0;
            $attachCount = rand(1, 3);

            $pivotData = [];
            for ($i = 0; $i < $attachCount; $i++) {
                $serviceId = $this->faker->randomElement($serviceIds);
                $quantity = rand(1, 3);
                $pricePerUnit = $this->faker->randomFloat(2, 10, 80);

                $calculatedTotal += $pricePerUnit * $quantity;

                $pivotData[] = [
                    'reservation_id' => $reservation->id,
                    'service_id'     => $serviceId,
                    'quantity'       => $quantity,
                    'price'          => $pricePerUnit,
                ];
            }

            // Bulk insert and update reservation total
            ReservationService::insert($pivotData);
            $reservation->total_amount = $calculatedTotal;
            $reservation->save();
        });
    }
}
