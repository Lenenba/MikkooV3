<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;

use Illuminate\Http\Request;
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
        return Inertia::render('settings/ParentProfile', [
            'parentProfile' => Auth::user()->parentProfile,
            'role'  => Auth::user()->isParent(),
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
        $profile = $user->parentProfile();
        // Attempt to update: update() ne lancera le save() que s’il y a des attributs modifiés
        $updated = $profile->update($data);

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
