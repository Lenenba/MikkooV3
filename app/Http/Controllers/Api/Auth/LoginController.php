<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => __('auth.failed'),
                'errors' => ['email' => [__('auth.failed')]],
            ], 422);
        }

        $deviceName = (string) ($data['device_name'] ?? 'mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->loadMissing(['address', 'parentProfile', 'babysitterProfile', 'media']);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }
}
