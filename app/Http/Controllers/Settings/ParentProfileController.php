<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Models\ParentProfile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Settings\ParentProfileRequest;

class ParentProfileController extends Controller
{
    /**
     * Show the form for creating a new parent profile.
     *
     * @return \Inertia\Response
     */
    public function edit()
    {
        $user = Auth::user();
        return Inertia::render('settings/ParentProfile', [
            'parentProfile' =>  $user->parentProfile,
            'address' =>  $user->address,
            'role'  =>  $user->isParent(),
        ]);
    }

    /**
     * Update the parent profile in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ParentProfileRequest $request)
    {
        // Validate & get only the filled data
        $data = $request->validated();

        // Get the authenticated user
        $user = Auth::user();
        // Get the user's profile
        /** @var ParentProfile $user */
        $profile = $user->parentProfile();
        // Attempt to update: update() ne lancera le save() que s’il y a des attributs modifiés
        $updated = $profile->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'birthdate'  => $data['birthdate'],
            'phone'      => $data['phone'],
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
