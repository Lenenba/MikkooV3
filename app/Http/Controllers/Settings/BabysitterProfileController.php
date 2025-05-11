<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Settings\BabysitterProfileRequest;

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
            'babysitterProfile' =>  $user->babysitterProfile,
            'address' =>  $user->address,
            'role'  =>  $user->isBabysitter(),
        ]);
    }

    /**
     * Update the babysitter profile in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BabysitterProfileRequest $request)
    {
        // Validate & get only the filled data
        $data = $request->validated();

        // Get the authenticated user
        $user = Auth::user();
        // Get the user's profile
        $profile = $user->babysitterProfile();
        // Attempt to update: update() ne lancera le save() que s’il y a des attributs modifiés
        $updated = $profile->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'birthdate'  => $data['birthdate'],
            'phone'      => $data['phone'],
            'bio'        => $data['bio'],
            'experience' => $data['experience'],
            'price_per_hour' => $data['price_per_hour'],
            'payment_frequency' => $data['payment_frequency'],
        ]);

        /** @var Address $user */
        $user->address()->updateOrCreate($data['address']);
        if ($updated) {
            return redirect()
                ->back()
                ->with('success', 'Profile details updated!');
        }

        // Aucune donnée n’a changé
        return redirect()
            ->back()
            ->with('info', 'No changes detected.');
    }
}
