<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\LaunchSeeder;
use Database\Seeders\ProdSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! class_exists(\Faker\Factory::class)) {
            $this->command->warn('Faker not available; running LaunchSeeder instead.');
            $this->call([
                LaunchSeeder::class,
            ]);
            return;
        }

        $this->call([
            ProdSeeder::class,
        ]);
    }
}
