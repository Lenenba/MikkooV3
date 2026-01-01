<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $locations = [
            [
                'country' => 'Canada',
                'city' => 'Montreal',
                'province' => 'QC',
                'latitude' => 45.5017,
                'longitude' => -73.5673,
            ],
            [
                'country' => 'Canada',
                'city' => 'Quebec',
                'province' => 'QC',
                'latitude' => 46.8139,
                'longitude' => -71.2080,
            ],
            [
                'country' => 'Canada',
                'city' => 'Toronto',
                'province' => 'ON',
                'latitude' => 43.6532,
                'longitude' => -79.3832,
            ],
            [
                'country' => 'Canada',
                'city' => 'Ottawa',
                'province' => 'ON',
                'latitude' => 45.4215,
                'longitude' => -75.6972,
            ],
            [
                'country' => 'Canada',
                'city' => 'Vancouver',
                'province' => 'BC',
                'latitude' => 49.2827,
                'longitude' => -123.1207,
            ],
            [
                'country' => 'Canada',
                'city' => 'Calgary',
                'province' => 'AB',
                'latitude' => 51.0447,
                'longitude' => -114.0719,
            ],
            [
                'country' => 'Belgium',
                'city' => 'Brussels',
                'province' => 'BRU',
                'latitude' => 50.8503,
                'longitude' => 4.3517,
            ],
            [
                'country' => 'Belgium',
                'city' => 'Antwerp',
                'province' => 'VAN',
                'latitude' => 51.2194,
                'longitude' => 4.4025,
            ],
            [
                'country' => 'Belgium',
                'city' => 'Ghent',
                'province' => 'VOV',
                'latitude' => 51.0543,
                'longitude' => 3.7174,
            ],
            [
                'country' => 'Belgium',
                'city' => 'Liege',
                'province' => 'WLG',
                'latitude' => 50.6326,
                'longitude' => 5.5797,
            ],
            [
                'country' => 'Belgium',
                'city' => 'Namur',
                'province' => 'WNA',
                'latitude' => 50.4674,
                'longitude' => 4.8718,
            ],
            [
                'country' => 'Switzerland',
                'city' => 'Zurich',
                'province' => 'ZH',
                'latitude' => 47.3769,
                'longitude' => 8.5417,
            ],
            [
                'country' => 'Switzerland',
                'city' => 'Geneva',
                'province' => 'GE',
                'latitude' => 46.2044,
                'longitude' => 6.1432,
            ],
            [
                'country' => 'Switzerland',
                'city' => 'Lausanne',
                'province' => 'VD',
                'latitude' => 46.5197,
                'longitude' => 6.6323,
            ],
            [
                'country' => 'Switzerland',
                'city' => 'Bern',
                'province' => 'BE',
                'latitude' => 46.9480,
                'longitude' => 7.4474,
            ],
            [
                'country' => 'Switzerland',
                'city' => 'Basel',
                'province' => 'BS',
                'latitude' => 47.5596,
                'longitude' => 7.5886,
            ],
        ];
        $location = $this->faker->randomElement($locations);

        return [
            'addressable_id'   => User::factory(),
            'addressable_type' => User::class,
            'street'           => $this->faker->streetAddress(),
            'city'             => $location['city'],
            'province'         => $location['province'],
            'postal_code'      => $this->faker->postcode(),
            'country'          => $location['country'],
            'latitude'         => $location['latitude'] + $this->faker->randomFloat(4, -0.05, 0.05),
            'longitude'        => $location['longitude'] + $this->faker->randomFloat(4, -0.05, 0.05),
        ];
    }
}
