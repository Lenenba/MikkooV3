<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $role = $user->isAdmin() ? 'superadmin' : ($user->isBabysitter() ? 'babysitter' : 'parent');

        $user->loadMissing(['address', 'parentProfile', 'babysitterProfile', 'media']);

        return response()->json([
            'role' => $role,
            'user' => new UserResource($user),
        ]);
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $user->loadMissing(['address', 'parentProfile', 'babysitterProfile', 'media']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
