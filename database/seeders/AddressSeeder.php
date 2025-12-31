<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function (User $user) {
            if (! $user->address()->exists()) {
                Address::factory()
                    ->for($user, 'addressable')
                    ->create();
            }
        });
    }
}
