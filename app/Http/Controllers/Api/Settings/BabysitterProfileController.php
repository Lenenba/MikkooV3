<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BabysitterProfileRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\BabysitterProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BabysitterProfileController extends Controller
{
    public function update(BabysitterProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        if (! $user || ! $user->isBabysitter()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $profile = $user->babysitterProfile;
        if (! $profile) {
            $profile = $user->babysitterProfile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birthdate' => $data['birthdate'],
                'phone' => $data['phone'],
                'bio' => $data['bio'] ?? null,
                'experience' => $data['experience'] ?? null,
                'price_per_hour' => $data['price_per_hour'],
                'payment_frequency' => $data['payment_frequency'],
            ]);
        }

        $profile->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birthdate' => $data['birthdate'],
            'phone' => $data['phone'],
            'bio' => $data['bio'] ?? null,
            'experience' => $data['experience'] ?? null,
            'price_per_hour' => $data['price_per_hour'],
            'payment_frequency' => $data['payment_frequency'],
        ]);
        $profile->save();

        $user->address()->updateOrCreate($data['address']);

        $settings = $profile->settings ?? [];
        if (array_key_exists('services', $data)) {
            $settings['services'] = $data['services'];
        }
        if (array_key_exists('availability', $data)) {
            $settings['availability'] = $data['availability'];
        }
        if (array_key_exists('availability_notes', $data)) {
            $settings['availability_notes'] = $data['availability_notes'];
        }
        $profile->settings = $settings;
        $profile->save();

        $user->loadMissing('address');

        return response()->json([
            'profile' => new BabysitterProfileResource($profile),
            'address' => AddressResource::make($user->address),
        ]);
    }
}
