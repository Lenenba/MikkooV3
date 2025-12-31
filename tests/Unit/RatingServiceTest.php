<?php

use App\Models\Reservation;
use App\Models\User;
use App\Services\RatingService;
use Illuminate\Auth\Access\AuthorizationException;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it aggregates babysitter ratings across reservations', function () {
    $babysitter = User::factory()->create();
    $parentOne = User::factory()->create();
    $parentTwo = User::factory()->create();

    $reservationOne = Reservation::factory()->create([
        'parent_id' => $parentOne->id,
        'babysitter_id' => $babysitter->id,
    ]);
    $reservationTwo = Reservation::factory()->create([
        'parent_id' => $parentTwo->id,
        'babysitter_id' => $babysitter->id,
    ]);

    $service = app(RatingService::class);
    $service->submitReservationRating($reservationOne, $parentOne, 4, 'Great job');
    $service->submitReservationRating($reservationTwo, $parentTwo, 2, 'Could be better');

    $summary = User::query()->withRatingSummary()->findOrFail($babysitter->id);

    expect((float) $summary->rating_avg)->toBe(3.0);
    expect((int) $summary->rating_count)->toBe(2);
});

test('it blocks ratings from users outside the reservation', function () {
    $babysitter = User::factory()->create();
    $parent = User::factory()->create();
    $stranger = User::factory()->create();

    $reservation = Reservation::factory()->create([
        'parent_id' => $parent->id,
        'babysitter_id' => $babysitter->id,
    ]);

    $service = app(RatingService::class);

    expect(fn () => $service->submitReservationRating($reservation, $stranger, 5, 'No access'))
        ->toThrow(AuthorizationException::class);
});
