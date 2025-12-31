<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Reservation;
use App\Services\RatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('search includes rating summary and recent reviews', function () {
    $babysitterRole = Role::factory()->create(['name' => env('BABYSITTER_ROLE_NAME', 'Babysitter')]);
    $parentRole = Role::factory()->create(['name' => env('PARENT_ROLE_NAME', 'Parent')]);

    $babysitter = User::factory()->create(['name' => 'Mina Sitter']);
    $parent = User::factory()->create(['name' => 'Parent Demo']);

    $babysitter->roles()->attach($babysitterRole->id);
    $parent->roles()->attach($parentRole->id);

    $babysitter->babysitterProfile()->create([
        'first_name' => 'Mina',
        'last_name' => 'Sitter',
        'birthdate' => now()->subYears(25)->toDateString(),
        'phone' => '555-101-2020',
        'bio' => 'Patient and organized babysitter.',
        'experience' => '4 years of childcare experience.',
        'price_per_hour' => 25,
        'payment_frequency' => 'weekly',
    ]);

    $parent->parentProfile()->create([
        'first_name' => 'Parent',
        'last_name' => 'Demo',
        'birthdate' => now()->subYears(33)->toDateString(),
        'phone' => '555-111-2222',
    ]);

    $reservation = Reservation::factory()->create([
        'parent_id' => $parent->id,
        'babysitter_id' => $babysitter->id,
    ]);

    $reservation->details()->update(['status' => 'confirmed']);

    app(RatingService::class)->submitReservationRating($reservation, $parent, 5, 'Great job');

    $this->actingAs($parent)
        ->get('/search')
        ->assertStatus(200)
        ->assertInertia(fn(Assert $page) => $page
            ->component('search/Index')
            ->has('babysitters.data', 1)
            ->has('babysitters.data.0.rating_avg')
            ->where('babysitters.data.0.rating_count', 1)
            ->has('babysitters.data.0.received_ratings', 1)
            ->where('babysitters.data.0.received_ratings.0.comment', 'Great job')
            ->where('babysitters.data.0.received_ratings.0.reviewer.name', $parent->name)
        );
});
