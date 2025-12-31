<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\BabysitterProfileRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BabysitterProfileController extends Controller
{
    /**
     * Show the form for creating a new babysitter profile.
     *
     * @return \Inertia\Response
     */
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('settings/BabysitterProfile', [
            'babysitterProfile' => $user->babysitterProfile,
            'address' => $user->address,
            'role' => $user->isBabysitter(),
        ]);
    }

    /**
     * Update the babysitter profile in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BabysitterProfileRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

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
            'last_name'  => $data['last_name'],
            'birthdate'  => $data['birthdate'],
            'phone'      => $data['phone'],
            'bio'        => $data['bio'] ?? null,
            'experience' => $data['experience'] ?? null,
            'price_per_hour' => $data['price_per_hour'],
            'payment_frequency' => $data['payment_frequency'],
        ]);
        $updated = $profile->isDirty();
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

        if ($updated) {
            return redirect()
                ->back()
                ->with('success', 'Profile details updated!');
        }

        return redirect()
            ->back()
            ->with('info', 'No changes detected.');
    }
}
