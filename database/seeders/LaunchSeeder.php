<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\BabysitterProfile;
use App\Models\Media;
use App\Models\ParentProfile;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LaunchSeeder extends Seeder
{
    /**
     * Seed a realistic dataset for preprod validation.
     */
    public function run(): void
    {
        $adminRoleName = env('SUPER_ADMIN_ROLE_NAME', 'SuperAdmin');
        $parentRoleName = env('PARENT_ROLE_NAME', 'Parent');
        $babysitterRoleName = env('BABYSITTER_ROLE_NAME', 'Babysitter');

        $adminRole = Role::firstOrCreate(['name' => $adminRoleName]);
        $parentRole = Role::firstOrCreate(['name' => $parentRoleName]);
        $babysitterRole = Role::firstOrCreate(['name' => $babysitterRoleName]);

        $this->command->info('Roles created or retrieved.');

        $this->call(ServiceCatalogSeeder::class);

        fake()->seed(20250307);

        $locationsByCountry = [
            'Switzerland' => [
                ['city' => 'Zurich', 'province' => 'ZH', 'latitude' => 47.3769, 'longitude' => 8.5417],
                ['city' => 'Geneva', 'province' => 'GE', 'latitude' => 46.2044, 'longitude' => 6.1432],
                ['city' => 'Lausanne', 'province' => 'VD', 'latitude' => 46.5197, 'longitude' => 6.6323],
                ['city' => 'Bern', 'province' => 'BE', 'latitude' => 46.9480, 'longitude' => 7.4474],
                ['city' => 'Basel', 'province' => 'BS', 'latitude' => 47.5596, 'longitude' => 7.5886],
            ],
            'Belgium' => [
                ['city' => 'Brussels', 'province' => 'BRU', 'latitude' => 50.8503, 'longitude' => 4.3517],
                ['city' => 'Antwerp', 'province' => 'VAN', 'latitude' => 51.2194, 'longitude' => 4.4025],
                ['city' => 'Ghent', 'province' => 'VOV', 'latitude' => 51.0543, 'longitude' => 3.7174],
                ['city' => 'Liege', 'province' => 'WLG', 'latitude' => 50.6326, 'longitude' => 5.5797],
                ['city' => 'Namur', 'province' => 'WNA', 'latitude' => 50.4674, 'longitude' => 4.8718],
            ],
            'Canada' => [
                ['city' => 'Montreal', 'province' => 'QC', 'latitude' => 45.5017, 'longitude' => -73.5673],
                ['city' => 'Quebec', 'province' => 'QC', 'latitude' => 46.8139, 'longitude' => -71.2080],
                ['city' => 'Toronto', 'province' => 'ON', 'latitude' => 43.6532, 'longitude' => -79.3832],
                ['city' => 'Ottawa', 'province' => 'ON', 'latitude' => 45.4215, 'longitude' => -75.6972],
                ['city' => 'Vancouver', 'province' => 'BC', 'latitude' => 49.2827, 'longitude' => -123.1207],
                ['city' => 'Calgary', 'province' => 'AB', 'latitude' => 51.0447, 'longitude' => -114.0719],
            ],
        ];

        $emailCounter = 1;
        $makeEmail = function (string $firstName, string $lastName) use (&$emailCounter): string {
            $slug = Str::slug("{$firstName}.{$lastName}", '.');
            if ($slug === '') {
                $slug = 'user';
            }
            $email = sprintf('%s.%04d@mikoo.test', $slug, $emailCounter);
            $emailCounter++;
            return $email;
        };

        $pickLocation = function (string $country) use ($locationsByCountry): array {
            return Arr::random($locationsByCountry[$country] ?? $locationsByCountry['Canada']);
        };

        $createUser = function (Role $role, string $country, ?string $email = null, ?string $firstName = null, ?string $lastName = null) use (
            $adminRoleName,
            $babysitterRoleName,
            $makeEmail,
            $pickLocation
        ): User {
            $firstName = $firstName ?? fake()->firstName();
            $lastName = $lastName ?? fake()->lastName();
            $email = $email ?? $makeEmail($firstName, $lastName);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => trim("{$firstName} {$lastName}"),
                    'password' => Hash::make('password'),
                ]
            );

            $user->roles()->syncWithoutDetaching([$role->id]);

            if ($role->name === $babysitterRoleName) {
                if (! $user->babysitterProfile()->exists()) {
                    BabysitterProfile::factory()
                        ->for($user)
                        ->state([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ])
                        ->create();
                }
            } else {
                if (! $user->parentProfile()->exists()) {
                    ParentProfile::factory()
                        ->for($user)
                        ->state([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ])
                        ->create();
                }
            }

            if (! $user->media()->where('collection_name', 'avatar')->exists()) {
                Media::factory()
                    ->for($user, 'mediaable')
                    ->state(['collection_name' => 'avatar'])
                    ->create();
            }

            if (! $user->address()->exists()) {
                $location = $pickLocation($country);
                Address::factory()
                    ->for($user, 'addressable')
                    ->state([
                        'city' => $location['city'],
                        'province' => $location['province'],
                        'country' => $country,
                        'latitude' => $location['latitude'] + fake()->randomFloat(4, -0.05, 0.05),
                        'longitude' => $location['longitude'] + fake()->randomFloat(4, -0.05, 0.05),
                    ])
                    ->create();
            }

            return $user;
        };

        $this->command->info('Seeding core accounts...');
        $createUser($adminRole, 'Switzerland', 'admin@mikoo.test', 'Amelie', 'Durand');

        $seedParents = [
            ['email' => 'parent.ch@mikoo.test', 'first' => 'Claire', 'last' => 'Muller', 'country' => 'Switzerland'],
            ['email' => 'parent.be@mikoo.test', 'first' => 'Julie', 'last' => 'Dupont', 'country' => 'Belgium'],
            ['email' => 'parent.ca@mikoo.test', 'first' => 'Emma', 'last' => 'Tremblay', 'country' => 'Canada'],
        ];

        $seedBabysitters = [
            ['email' => 'sitter.ch@mikoo.test', 'first' => 'Sofia', 'last' => 'Rossi', 'country' => 'Switzerland'],
            ['email' => 'sitter.be@mikoo.test', 'first' => 'Lina', 'last' => 'De Smet', 'country' => 'Belgium'],
            ['email' => 'sitter.ca@mikoo.test', 'first' => 'Maya', 'last' => 'Larson', 'country' => 'Canada'],
        ];

        $parentsByCountry = [
            'Switzerland' => [],
            'Belgium' => [],
            'Canada' => [],
        ];
        foreach ($seedParents as $seed) {
            $user = $createUser($parentRole, $seed['country'], $seed['email'], $seed['first'], $seed['last']);
            $parentsByCountry[$seed['country']][] = $user;
        }

        $babysittersByCountry = [
            'Switzerland' => [],
            'Belgium' => [],
            'Canada' => [],
        ];
        foreach ($seedBabysitters as $seed) {
            $user = $createUser($babysitterRole, $seed['country'], $seed['email'], $seed['first'], $seed['last']);
            $babysittersByCountry[$seed['country']][] = $user;
        }

        $babysitterTargets = [
            'Switzerland' => 300,
            'Belgium' => 200,
            'Canada' => 200,
        ];

        $parentTargets = [
            'Switzerland' => 120,
            'Belgium' => 80,
            'Canada' => 80,
        ];

        $this->command->info('Seeding parents...');
        foreach ($parentTargets as $country => $count) {
            $remaining = max(0, $count - count($parentsByCountry[$country]));
            for ($i = 0; $i < $remaining; $i++) {
                $parentsByCountry[$country][] = $createUser($parentRole, $country);
            }
            $this->command->info("Parents seeded for {$country}: {$count}");
        }

        $this->command->info('Seeding babysitters...');
        foreach ($babysitterTargets as $country => $count) {
            $remaining = max(0, $count - count($babysittersByCountry[$country]));
            for ($i = 0; $i < $remaining; $i++) {
                $babysittersByCountry[$country][] = $createUser($babysitterRole, $country);
            }
            $this->command->info("Babysitters seeded for {$country}: {$count}");
        }

        $this->command->info('Creating reservations for the last 3 months...');
        $reservationsPerMonth = 2;
        $monthsBack = 3;

        foreach ($parentsByCountry as $country => $parents) {
            $countryBabysitters = $babysittersByCountry[$country];
            if (empty($countryBabysitters)) {
                continue;
            }

            foreach ($parents as $parent) {
                for ($monthOffset = 0; $monthOffset < $monthsBack; $monthOffset++) {
                    $period = Carbon::now()->subMonths($monthOffset);
                    $start = $period->copy()->startOfMonth();
                    $end = $period->copy()->endOfMonth();

                    for ($j = 0; $j < $reservationsPerMonth; $j++) {
                        $randomBabysitter = Arr::random($countryBabysitters);
                        $randomDate = fake()->dateTimeBetween($start, $end);

                        Reservation::factory()
                            ->state([
                                'parent_id' => $parent->id,
                                'babysitter_id' => $randomBabysitter->id,
                                'created_at' => $randomDate,
                                'updated_at' => $randomDate,
                            ])
                            ->create();
                    }
                }
            }
        }

        $this->call([
            BabysitterServicesSeeder::class,
            AnnouncementSeeder::class,
            RatingSeeder::class,
        ]);
    }
}
