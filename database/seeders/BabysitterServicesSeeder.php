<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class BabysitterServicesSeeder extends Seeder
{
    /**
     * Seed services for babysitters using the shared catalog.
     */
    public function run(): void
    {
        $babysitters = User::babysitters()->get();
        if ($babysitters->isEmpty()) {
            $this->command->warn('No babysitters found. Skipping babysitter services seeding.');
            return;
        }

        $catalog = Service::query()
            ->whereNull('user_id')
            ->orderBy('name')
            ->get();

        if ($catalog->isEmpty()) {
            $this->command->warn('No catalog services found. Skipping babysitter services seeding.');
            return;
        }

        $created = 0;
        foreach ($babysitters as $babysitter) {
            $selected = $catalog->shuffle()->take(min(3, $catalog->count()));

            foreach ($selected as $service) {
                $price = mt_rand(1200, 3000) / 100;
                $record = Service::withTrashed()->firstOrCreate(
                    [
                        'user_id' => $babysitter->id,
                        'name' => $service->name,
                    ],
                    [
                        'description' => $service->description,
                        'price' => $price,
                    ]
                );

                if ($record->trashed()) {
                    $record->restore();
                }

                $record->update([
                    'description' => $service->description,
                    'price' => $price,
                ]);

                $created++;
            }
        }

        $this->command->info("Babysitter services seeded: {$created}.");
    }
}
