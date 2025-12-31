<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LaunchSeeder extends Seeder
{
    /**
     * Seed a full dataset for local validation.
     */
    public function run(): void
    {
        $this->call([
            DevSeeder::class,
            RatingSeeder::class,
        ]);
    }
}
