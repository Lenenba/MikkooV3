<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Role;
use App\Models\Media;
use App\Models\User;
use App\Models\ParentProfile;
use App\Models\BabysitterProfile;
use App\Models\Reservation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class DevSeeder extends Seeder
{
    /**
     * Seed the application's database with test users, profiles, reservations, and media.
     */
    public function run(): void
    {
        // Create or retrieve roles
        $adminRole      = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $parentRole     = Role::firstOrCreate(['name' => 'Parent']);
        $babysitterRole = Role::firstOrCreate(['name' => 'Babysitter']);

        $this->command->info('Roles created or retrieved.');

        $this->call(ServiceCatalogSeeder::class);

        /**
         * Helper to create a user with the correct profile and media.
         *
         * @param string $email
         * @param string $name
         * @param Role   $role
         * @return User
         */
        $createUser = function (string $email, string $name, Role $role): User {
            // Create or retrieve the user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $name,
                    'password' => Hash::make('password'), // default password
                ]
            );

            // Assign the role
            $user->roles()->syncWithoutDetaching([$role->id]);

            // Create profile if it does not exist
            if ($role->name === 'Babysitter') {
                if (! $user->babysitterProfile()->exists()) {
                    BabysitterProfile::factory()
                        ->for($user)
                        ->create();
                }
            } else {
                // Parent or SuperAdmin get a ParentProfile
                if (! $user->parentProfile()->exists()) {
                    ParentProfile::factory()
                        ->for($user)
                        ->create();
                }
            }

            // Create a default avatar media if none exists
            if (! $user->media()->where('collection_name', 'avatar')->exists()) {
                Media::factory()
                    ->for($user, 'mediaable')
                    ->state(['collection_name' => 'avatar'])
                    ->create();
            }

            // Create an address if none exists
            if (! $user->address()->exists()) {
                Address::factory()
                    ->for($user, 'addressable')
                    ->create();
            }

            return $user;
        };

        // Seed admin and parent users
        $this->command->info('Seeding admin and parent users...');
        $createUser('superadmin@example.com', 'Admin User', $adminRole);
        $createdParents = [];
        $createdParents[] = $createUser('parent1@example.com', 'Parent One', $parentRole);
        $createdParents[] = $createUser('parent2@example.com', 'Parent Two', $parentRole);
        for ($i = 3; $i <= 5; $i++) {
            $createdParents[] = $createUser("parent{$i}@example.com", "Parent {$i}", $parentRole);
        }

        // Seed babysitter users
        $this->command->info('Seeding babysitter users...');
        $createdBabysitters = [];
        for ($i = 1; $i <= 10; $i++) {
            $createdBabysitters[] = $createUser("babysitter{$i}@example.com", "Babysitter {$i}", $babysitterRole);
        }
        $this->command->info(count($createdBabysitters) . ' babysitters created.');

        // Create reservations over the last 4 months
        $this->command->info('Creating reservations over the last 4 months...');
        if (empty($createdParents) || empty($createdBabysitters)) {
            $this->command->warn('No parents or babysitters available. Skipping reservation seeding.');
            return;
        }


        foreach ($createdParents as $parent) {
            // 3 reservations per month for each of the last 4 months
            for ($monthOffset = 0; $monthOffset < 4; $monthOffset++) {
                $period = Carbon::now()->subMonths($monthOffset);
                $start  = $period->copy()->startOfMonth();
                $end    = $period->copy()->endOfMonth();

                for ($j = 0; $j < 3; $j++) {
                    $randomBabysitter = Arr::random($createdBabysitters);
                    $randomDate       = fake()->dateTimeBetween($start, $end);

                    Reservation::factory()
                        ->state([
                            'parent_id'     => $parent->id,
                            'babysitter_id' => $randomBabysitter->id,
                            // Override timestamps to test monthly stats
                            'created_at'    => $randomDate,
                            'updated_at'    => $randomDate,
                        ])
                        ->create();
                }
            }

            $this->command->info("Created 12 reservations for parent: {$parent->name}");
        }

        $this->command->info('Reservations seeding completed successfully!');
    }
}
