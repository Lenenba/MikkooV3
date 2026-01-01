<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Controllers\Traits\UtilsPhotoConverter;
use App\Models\Media;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use UtilsPhotoConverter;
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $role = null;
        $parentProfile = null;
        $babysitterProfile = null;
        $address = null;
        $media = [];

        if ($user) {
            $role = $user->isBabysitter() ? 'babysitter' : 'parent';
            $parentProfile = $user->isParent() ? $user->parentProfile : null;
            $babysitterProfile = $user->isBabysitter() ? $user->babysitterProfile : null;
            $address = $user->address;
            $media = $user->media()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn(Media $item) => [
                    'id'              => $item->id,
                    'url'             => $this->convertToWebp($item->file_path),
                    'collection_name' => $item->collection_name,
                    'mime_type'       => $item->mime_type,
                    'is_profile'      => $item->is_profile_picture,
                ])
                ->values()
                ->all();
        }

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'role' => $role,
            'parentProfile' => $parentProfile,
            'babysitterProfile' => $babysitterProfile,
            'address' => $address,
            'media' => $media,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
