<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Service;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\ReservationService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon; // Ajout pour les timestamps

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
            'notes'         => $this->faker->boolean(70) ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Configure the factory to create related services and details.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Reservation $reservation) {

            ReservationDetail::factory()
                ->count(rand(1, 3))
                ->create(['reservation_id' => $reservation->id]);

            $serviceIds = Service::pluck('id')->toArray();

            if (empty($serviceIds)) {
                Service::factory()->count(mt_rand(2, 5))->create();
                $serviceIds = Service::pluck('id')->toArray();
            }

            if (!empty($serviceIds)) {
                $reservationServicesData = [];
                $calculatedTotalAmount = 0;
                $numberOfServicesToAttach = rand(1, 3);

                for ($i = 0; $i < $numberOfServicesToAttach; $i++) {
                    $serviceId = $this->faker->randomElement($serviceIds);

                    $quantity = rand(1, 3); // Quantité numérique

                    $pricePerUnit = $this->faker->randomFloat(2, 10, 80); // Ex: prix entre 10.00 et 80.00

                    $calculatedTotalAmount += $pricePerUnit * $quantity;

                    $reservationServicesData[] = [
                        'reservation_id' => $reservation->id,
                        'service_id'     => $serviceId,
                        'quantity'       => (string)$quantity,
                        'price'          => $pricePerUnit,
                    ];
                }

                if (!empty($reservationServicesData)) {
                    ReservationService::insert($reservationServicesData);
                }

                // Mettre à jour le total_amount de la réservation et sauvegarder
                $reservation->total_amount = $calculatedTotalAmount;
                $reservation->save();
            }
        });
    }
}
