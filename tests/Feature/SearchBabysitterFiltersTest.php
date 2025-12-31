<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Address;
use App\Models\Reservation;
use App\Models\BabysitterProfile;
use App\Services\RatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createBabysitter(Role $role, array $profileOverrides = [], array $addressOverrides = []): User
{
    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    BabysitterProfile::factory()
        ->for($user)
        ->create(array_merge([
            'price_per_hour' => 25,
            'payment_frequency' => 'weekly',
        ], $profileOverrides));

    Address::factory()
        ->for($user, 'addressable')
        ->create($addressOverrides);

    return $user;
}

function createParent(Role $role, array $addressOverrides = []): User
{
    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    Address::factory()
        ->for($user, 'addressable')
        ->create($addressOverrides);

    return $user;
}

test('filters by city and country', function () {
    $babysitterRole = Role::factory()->create(['name' => env('BABYSITTER_ROLE_NAME', 'Babysitter')]);
    $parentRole = Role::factory()->create(['name' => env('PARENT_ROLE_NAME', 'Parent')]);

    $parent = createParent($parentRole);
    $montreal = createBabysitter($babysitterRole, [], [
        'city' => 'Montreal',
        'country' => 'Canada',
    ]);
    createBabysitter($babysitterRole, [], [
        'city' => 'Paris',
        'country' => 'France',
    ]);

    $this->actingAs($parent)
        ->get('/search?city=Montreal&country=Canada')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('search/Index')
            ->has('babysitters.data', 1)
            ->where('babysitters.data.0.id', $montreal->id)
        );
});

test('filters by min and max price', function () {
    $babysitterRole = Role::factory()->create(['name' => env('BABYSITTER_ROLE_NAME', 'Babysitter')]);
    $parentRole = Role::factory()->create(['name' => env('PARENT_ROLE_NAME', 'Parent')]);

    $parent = createParent($parentRole);
    createBabysitter($babysitterRole, ['price_per_hour' => 15]);
    $target = createBabysitter($babysitterRole, ['price_per_hour' => 30]);
    createBabysitter($babysitterRole, ['price_per_hour' => 50]);

    $this->actingAs($parent)
        ->get('/search?min_price=20&max_price=40')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('search/Index')
            ->has('babysitters.data', 1)
            ->where('babysitters.data.0.id', $target->id)
        );
});

test('filters by min rating', function () {
    $babysitterRole = Role::factory()->create(['name' => env('BABYSITTER_ROLE_NAME', 'Babysitter')]);
    $parentRole = Role::factory()->create(['name' => env('PARENT_ROLE_NAME', 'Parent')]);

    $parent = createParent($parentRole);
    $high = createBabysitter($babysitterRole);
    $low = createBabysitter($babysitterRole);

    $reservationHigh = Reservation::factory()->create([
        'parent_id' => $parent->id,
        'babysitter_id' => $high->id,
    ]);
    $reservationHigh->details()->update(['status' => 'confirmed']);
    app(RatingService::class)->submitReservationRating($reservationHigh, $parent, 5, 'Excellent');

    $reservationLow = Reservation::factory()->create([
        'parent_id' => $parent->id,
        'babysitter_id' => $low->id,
    ]);
    $reservationLow->details()->update(['status' => 'confirmed']);
    app(RatingService::class)->submitReservationRating($reservationLow, $parent, 3, 'Ok');

    $this->actingAs($parent)
        ->get('/search?min_rating=4')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('search/Index')
            ->has('babysitters.data', 1)
            ->where('babysitters.data.0.id', $high->id)
        );
});

test('sorts by distance for parent', function () {
    $babysitterRole = Role::factory()->create(['name' => env('BABYSITTER_ROLE_NAME', 'Babysitter')]);
    $parentRole = Role::factory()->create(['name' => env('PARENT_ROLE_NAME', 'Parent')]);

    $parent = createParent($parentRole, [
        'city' => 'Montreal',
        'country' => 'Canada',
        'latitude' => 45.5017,
        'longitude' => -73.5673,
    ]);

    $near = createBabysitter($babysitterRole, [], [
        'city' => 'Montreal',
        'country' => 'Canada',
        'latitude' => 45.5025,
        'longitude' => -73.5650,
    ]);
    createBabysitter($babysitterRole, [], [
        'city' => 'Quebec',
        'country' => 'Canada',
        'latitude' => 46.8139,
        'longitude' => -71.2080,
    ]);

    $this->actingAs($parent)
        ->get('/search?sort=distance')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('search/Index')
            ->has('babysitters.data', 2)
            ->where('babysitters.data.0.id', $near->id)
        );
});
