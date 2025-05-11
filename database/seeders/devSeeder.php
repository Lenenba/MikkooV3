<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Media;
use App\Models\User;
use App\Models\ParentProfile;
use App\Models\BabysitterProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            $user->roles()->sync([$role->id]);

            // Create the correct profile via factory
            if ($role->name === 'Babysitter') {
                BabysitterProfile::factory()
                    ->for($user)
                    ->create();
            } else {
                ParentProfile::factory()
                    ->for($user)
                    ->create();
            }

            // Create a default avatar media via factory
            Media::factory()
                ->for($user, 'mediaable')
                ->state(['collection_name' => 'avatar'])
                ->create();

            return $user;
        };

        // Seed admin and parents
        $createUser('superadmin@example.com', 'Admin User', $adminRole);
        $createUser('parent1@example.com',   'Parent One',   $parentRole);
        $createUser('parent2@example.com',   'Parent Two',   $parentRole);

        // Seed babysitters
        $createUser('babysitter1@example.com', 'Babysitter One', $babysitterRole);
        $createUser('babysitter2@example.com', 'Babysitter Two', $babysitterRole);
    }
}
