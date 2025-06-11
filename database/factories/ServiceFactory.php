<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            // Name of the service (e.g. 'Babysitting', 'Meal Prep', etc.)
            'name'        => $this->faker->unique()->randomElement([
                'Babysitting',
                'Bedtime Routine',
                'Meal Preparation',
                'Homework Help',
                'Overnight Care',
                'Pet Care',
                'House Cleaning',
                'Laundry Service',
                'Grocery Shopping',
                'Errand Running',
                'Tutoring',
                'Transportation',
                'Event Planning',
                'Personal Shopping',
                'Senior Care',
                'Companionship',
                'House Sitting',
            ]),
            // Description text
            'description' => $this->faker->paragraph(),
            // Price of the service
            'price'       => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
