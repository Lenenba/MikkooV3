<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LaunchSeeder extends Seeder
{
    /**
     * Seed a realistic dataset for preprod validation.
     */
    public function run(): void
    {
        $fakerAvailable = class_exists(\Faker\Factory::class);
        $faker = $fakerAvailable ? \Faker\Factory::create('fr_FR') : null;

        if (! $fakerAvailable) {
            $this->command->warn('Faker not available; using deterministic data.');
        }

        $adminRoleName = env('SUPER_ADMIN_ROLE_NAME', 'SuperAdmin');
        $parentRoleName = env('PARENT_ROLE_NAME', 'Parent');
        $babysitterRoleName = env('BABYSITTER_ROLE_NAME', 'Babysitter');

        $adminRole = Role::firstOrCreate(['name' => $adminRoleName]);
        $parentRole = Role::firstOrCreate(['name' => $parentRoleName]);
        $babysitterRole = Role::firstOrCreate(['name' => $babysitterRoleName]);

        $this->command->info('Roles created or retrieved.');

        $this->call(ServiceCatalogSeeder::class);

        mt_srand(20250307);

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

        $firstNames = [
            'Emma', 'Lina', 'Sofia', 'Maya', 'Noah', 'Lucas', 'Liam', 'Leo', 'Nina', 'Sara',
            'Mila', 'Ethan', 'Ava', 'Chloe', 'Mason', 'Ella', 'Jules', 'Hugo', 'Adam', 'Luca',
        ];

        $lastNames = [
            'Martin', 'Dubois', 'Muller', 'Rossi', 'Dupont', 'Bernard', 'Schmidt', 'Weber', 'Lambert', 'Tremblay',
            'Roy', 'Gagnon', 'Moreau', 'Laurent', 'Brun', 'Peterson', 'Meyer', 'Fischer', 'Simon', 'Lopez',
        ];

        $bioSnippets = [
            'Patient and calm with kids of all ages.',
            'Montessori inspired activities and gentle routines.',
            'Outdoor play and creative games every day.',
            'Homework support and bedtime routines.',
            'Focused on safety, respect, and kindness.',
            'Comfortable with toddlers and school age kids.',
        ];

        $experienceSnippets = [
            '3 years of babysitting in family homes.',
            'Former camp counselor with first aid basics.',
            'Experience with twins and bedtime routines.',
            'After school care and activity planning.',
            'Support with homework and reading practice.',
            'Weekend and evening availability.',
        ];

        $reservationNotes = [
            'After school care and snack time.',
            'Evening babysitting with bedtime routine.',
            'Weekend support for two children.',
            'Help with homework and light dinner.',
            'Short booking with flexible end time.',
        ];

        $streetNames = ['Oak', 'Maple', 'Cedar', 'Pine', 'Elm', 'Lake', 'Hill', 'Park', 'Sunset', 'River'];
        $streetTypes = ['St', 'Ave', 'Rd', 'Ln', 'Blvd'];

        $paymentFrequencies = ['per_task', 'daily', 'weekly', 'biweekly', 'monthly'];

        $priceBands = [
            'Switzerland' => [28, 45],
            'Belgium' => [15, 28],
            'Canada' => [18, 32],
        ];

        $randomElement = static function (array $items) {
            return $items[mt_rand(0, count($items) - 1)];
        };

        $randomBool = static function (int $percent): bool {
            return mt_rand(1, 100) <= $percent;
        };

        $randomName = function () use ($faker, $randomElement, $firstNames, $lastNames): array {
            if ($faker) {
                return [$faker->firstName(), $faker->lastName()];
            }

            return [$randomElement($firstNames), $randomElement($lastNames)];
        };

        $randomBirthdate = function (int $minAge, int $maxAge) use ($faker): string {
            if ($faker) {
                return $faker->dateTimeBetween("-{$maxAge} years", "-{$minAge} years")->format('Y-m-d');
            }

            $min = Carbon::now()->subYears($maxAge)->startOfDay()->timestamp;
            $max = Carbon::now()->subYears($minAge)->endOfDay()->timestamp;
            return Carbon::createFromTimestamp(mt_rand($min, $max))->format('Y-m-d');
        };

        $randomStreet = function () use ($randomElement, $streetNames, $streetTypes): string {
            return sprintf('%d %s %s', mt_rand(10, 220), $randomElement($streetNames), $randomElement($streetTypes));
        };

        $randomPostal = function (string $country): string {
            if ($country === 'Canada') {
                $letters = 'ABCEGHJKLMNPRSTVXY';
                return sprintf(
                    '%s%d%s %d%s%d',
                    $letters[mt_rand(0, strlen($letters) - 1)],
                    mt_rand(0, 9),
                    $letters[mt_rand(0, strlen($letters) - 1)],
                    mt_rand(0, 9),
                    $letters[mt_rand(0, strlen($letters) - 1)],
                    mt_rand(0, 9)
                );
            }

            return (string) mt_rand(1000, 9999);
        };

        $randomPhone = function (string $country): string {
            if ($country === 'Switzerland') {
                return sprintf('+41 %d %03d %02d %02d', mt_rand(70, 79), mt_rand(100, 999), mt_rand(10, 99), mt_rand(10, 99));
            }

            if ($country === 'Belgium') {
                return sprintf('+32 %d %02d %02d %02d', mt_rand(470, 499), mt_rand(10, 99), mt_rand(10, 99), mt_rand(10, 99));
            }

            return sprintf('+1 %03d %03d %04d', mt_rand(200, 999), mt_rand(200, 999), mt_rand(0, 9999));
        };

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

        $createUser = function (
            Role $role,
            string $country,
            ?string $email = null,
            ?string $firstName = null,
            ?string $lastName = null
        ) use (
            $babysitterRoleName,
            $bioSnippets,
            $experienceSnippets,
            $makeEmail,
            $paymentFrequencies,
            $pickLocation,
            $priceBands,
            $randomBirthdate,
            $randomElement,
            $randomName,
            $randomPhone,
            $randomPostal,
            $randomStreet
        ): User {
            if (! $firstName || ! $lastName) {
                [$firstName, $lastName] = $randomName();
            }

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
                    [$minPrice, $maxPrice] = $priceBands[$country] ?? [15, 30];
                    $user->babysitterProfile()->create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'birthdate' => $randomBirthdate(20, 48),
                        'phone' => $randomPhone($country),
                        'bio' => $randomElement($bioSnippets),
                        'experience' => $randomElement($experienceSnippets),
                        'price_per_hour' => mt_rand($minPrice, $maxPrice),
                        'payment_frequency' => $randomElement($paymentFrequencies),
                    ]);
                }
            } else {
                if (! $user->parentProfile()->exists()) {
                    $user->parentProfile()->create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'birthdate' => $randomBirthdate(28, 55),
                        'phone' => $randomPhone($country),
                    ]);
                }
            }

            if (! $user->address()->exists()) {
                $location = $pickLocation($country);
                $user->address()->create([
                    'street' => $randomStreet(),
                    'city' => $location['city'],
                    'province' => $location['province'],
                    'postal_code' => $randomPostal($country),
                    'country' => $country,
                    'latitude' => $location['latitude'] + (mt_rand(-50, 50) / 1000),
                    'longitude' => $location['longitude'] + (mt_rand(-50, 50) / 1000),
                ]);
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

        $this->call([
            BabysitterServicesSeeder::class,
        ]);

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
                        $randomDate = Carbon::createFromTimestamp(mt_rand($start->timestamp, $end->timestamp));
                        $startHour = mt_rand(8, 18);
                        $startTime = Carbon::createFromTime($startHour, 0);
                        $endTime = $startTime->copy()->addHours(mt_rand(2, 5));
                        $status = $randomBool(70) ? 'confirmed' : 'pending';

                        $reservation = Reservation::create([
                            'parent_id' => $parent->id,
                            'babysitter_id' => $randomBabysitter->id,
                            'total_amount' => 0,
                            'notes' => $randomElement($reservationNotes),
                        ]);

                        $reservation->timestamps = false;
                        $reservation->created_at = $randomDate;
                        $reservation->updated_at = $randomDate;
                        $reservation->save();

                        ReservationDetail::create([
                            'reservation_id' => $reservation->id,
                            'date' => $randomDate->format('Y-m-d'),
                            'start_time' => $startTime->format('H:i:s'),
                            'end_time' => $endTime->format('H:i:s'),
                            'status' => $status,
                        ]);

                        $servicePool = Service::query()
                            ->where('user_id', $randomBabysitter->id)
                            ->get();

                        if ($servicePool->isEmpty()) {
                            $servicePool = Service::query()->whereNull('user_id')->get();
                        }

                        if ($servicePool->isEmpty()) {
                            continue;
                        }

                        $take = min(mt_rand(1, 3), $servicePool->count());
                        $selected = $servicePool->random($take);
                        if ($selected instanceof Service) {
                            $selected = collect([$selected]);
                        }

                        $total = 0;
                        foreach ($selected as $service) {
                            $quantity = mt_rand(1, 3);
                            $unitPrice = (float) $service->price;
                            if ($unitPrice <= 0) {
                                $unitPrice = mt_rand(12, 30);
                            }
                            $lineTotal = $unitPrice * $quantity;
                            $total += $lineTotal;

                            DB::table('reservation_services')->insert([
                                'reservation_id' => $reservation->id,
                                'service_id' => $service->id,
                                'quantity' => $quantity,
                                'total' => $lineTotal,
                                'created_at' => $randomDate,
                                'updated_at' => $randomDate,
                            ]);
                        }

                        $reservation->update(['total_amount' => $total]);
                    }
                }
            }
        }

        $this->call([
            AnnouncementSeeder::class,
            RatingSeeder::class,
        ]);
    }
}
