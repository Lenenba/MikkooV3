<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ParentProfileRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        if (! $user || ! $user->isParent()) {
            abort(403);
        }

        return Inertia::render('settings/ParentProfile', [
            'parentProfile' => $user->parentProfile,
            'address' => $user->address,
            'role' => $user->isParent(),
        ]);
    }

    /**
     * Update the parent profile in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ParentProfileRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        if (! $user || ! $user->isParent()) {
            abort(403);
        }

        $profile = $user->parentProfile;
        if (! $profile) {
            $profile = $user->parentProfile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birthdate' => $data['birthdate'],
                'phone' => $data['phone'],
            ]);
        }

        $profile->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birthdate' => $data['birthdate'],
            'phone' => $data['phone'],
        ]);
        $updated = $profile->isDirty();
        $profile->save();

        $user->address()->updateOrCreate($data['address']);

        $settings = $profile->settings ?? [];
        if (array_key_exists('preferences', $data)) {
            $settings['preferences'] = $data['preferences'];
        }
        if (array_key_exists('availability', $data)) {
            $settings['availability'] = $data['availability'];
        }
        if (array_key_exists('availability_notes', $data)) {
            $settings['availability_notes'] = $data['availability_notes'];
        }
        if (array_key_exists('children', $data)) {
            $children = collect($data['children'])
                ->map(function (array $child) {
                    $name = trim((string) ($child['name'] ?? ''));
                    $age = $child['age'] ?? null;
                    $allergies = trim((string) ($child['allergies'] ?? ''));
                    $details = trim((string) ($child['details'] ?? ''));
                    $photo = $child['photo'] ?? null;

                    return [
                        'name' => $name !== '' ? $name : null,
                        'age' => is_numeric($age) ? (int) $age : null,
                        'allergies' => $allergies !== '' ? $allergies : null,
                        'details' => $details !== '' ? $details : null,
                        'photo' => is_string($photo) && $photo !== '' ? $photo : null,
                    ];
                })
                ->filter(function (array $child) {
                    return $child['name'] !== null
                        || $child['age'] !== null
                        || $child['allergies'] !== null
                        || $child['details'] !== null
                        || $child['photo'] !== null;
                })
                ->values();

            $settings['children'] = $children->all();
            $settings['children_count'] = $children->count();
            $settings['children_ages'] = $children
                ->pluck('age')
                ->filter(fn ($age) => $age !== null)
                ->map(fn ($age) => (string) $age)
                ->implode(', ');
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
