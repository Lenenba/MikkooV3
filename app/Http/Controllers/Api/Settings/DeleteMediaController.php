<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DeleteMediaController extends Controller
{
    public function __invoke(int $mediaId): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $media = $user->media->find($mediaId);
        if (! $media) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        if ($media->is_profile_picture) {
            return response()->json(['message' => __('flash.profile.photo_cannot_delete')], 422);
        }

        $media->delete();

        return response()->json(['message' => __('flash.profile.photo_deleted')]);
    }
}
