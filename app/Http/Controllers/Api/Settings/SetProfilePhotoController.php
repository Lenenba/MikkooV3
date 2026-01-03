<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetProfilePhotoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $media = $user->media->find($data['media_id']);
        if (! $media) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($media->is_profile_picture) {
            return response()->json(['message' => __('flash.profile.photo_already_set')]);
        }

        $previousProfilePicture = $user->media->where('is_profile_picture', true)->first();
        if ($previousProfilePicture) {
            $previousProfilePicture->update([
                'is_profile_picture' => false,
            ]);
        }

        $media->update([
            'is_profile_picture' => true,
        ]);

        return response()->json([
            'message' => __('flash.profile.photo_updated'),
        ]);
    }
}
