<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Role;
use App\Models\Media;
use App\Models\User;
use App\Models\ParentProfile;
use App\Models\BabysitterProfile;
use App\Models\Reservation; // Assurez-vous d'importer le modèle Reservation
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr; // Pour Arr::random()

class DevSeeder extends Seeder
{
    /**
     * Seed the application's database with test users, profiles, and media.
     *
     * @return void
     */
    public function run(): void
    {
        // Create or retrieve roles
        $adminRole      = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $parentRole     = Role::firstOrCreate(['name' => 'Parent']);
        $babysitterRole = Role::firstOrCreate(['name' => 'Babysitter']);

        $this->command->info('Roles created or retrieved.');

        /**
         * Helper to create a user with a profile and media.
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
            $user->roles()->syncWithoutDetaching([$role->id]); // syncWithoutDetaching est plus sûr si la seeder est relancée

            // Create the correct profile via factory
            // Vérifier si un profil existe déjà pour cet utilisateur avant d'en créer un nouveau
            if ($role->name === 'Babysitter') {
                if (!$user->babysitterProfile()->exists()) {
                    BabysitterProfile::factory()
                        ->for($user)
                        ->create();
                }
            } elseif ($role->name === 'Parent' || $role->name === 'SuperAdmin') { // SuperAdmin aura un ParentProfile ici
                if (!$user->parentProfile()->exists()) {
                    ParentProfile::factory()
                        ->for($user)
                        ->create();
                }
            }


            // Create a default avatar media via factory, if none exists
            if (!$user->media()->where('collection_name', 'avatar')->exists()) {
                Media::factory()
                    ->for($user, 'mediaable')
                    ->state(['collection_name' => 'avatar'])
                    ->create();
            }

            // Create an address via factory, if none exists
            if (!$user->address()->exists()) {
                Address::factory()
                    ->for($user, 'addressable')
                    ->create();
            }

            return $user;
        };

        // Arrays pour stocker les utilisateurs créés
        $createdParents = [];
        $createdBabysitters = [];

        // Seed admin and parents
        $this->command->info('Seeding admin and parent users...');
        $createUser('superadmin@example.com', 'Admin User', $adminRole); // L'admin aura un ParentProfile selon la logique actuelle
        $createdParents[] = $createUser('parent1@example.com',   'Parent One',    $parentRole);
        $createdParents[] = $createUser('parent2@example.com',   'Parent Two',    $parentRole);
        // Ajouter plus de parents si nécessaire
        for ($i = 3; $i <= 5; $i++) {
            $createdParents[] = $createUser('parent' . $i . '@example.com', 'Parent ' . $i, $parentRole);
        }


        // Seed babysitters
        $this->command->info('Seeding babysitter users...');
        for ($i = 1; $i <= 10; $i++) {
            $createdBabysitters[] = $createUser('babysitter' . $i . '@example.com', 'Babysitter ' . $i, $babysitterRole);
        }
        $this->command->info(count($createdBabysitters) . ' babysitters created.');


        // --- Logique pour créer des réservations ---
        $this->command->info('Creating reservations...');

        if (empty($createdParents) || empty($createdBabysitters)) {
            $this->command->warn('No parents or babysitters available to create reservations. Skipping reservation seeding.');
            return;
        }

        foreach ($createdParents as $parent) {
            // Chaque parent fait 3 réservations
            $reservationsToCreate = 3;
            for ($j = 0; $j < $reservationsToCreate; $j++) {
                if (empty($createdBabysitters)) continue;

                $randomBabysitter = Arr::random($createdBabysitters);

                Reservation::factory()->create([
                    'parent_id'     => $parent->id,
                    'babysitter_id' => $randomBabysitter->id,
                ]);
            }
            $this->command->info("Successfully created {$reservationsToCreate} reservations for parent: {$parent->name} (ID: {$parent->id})");
        }

        $this->command->info('Reservations seeding completed successfully!');
    }
}
