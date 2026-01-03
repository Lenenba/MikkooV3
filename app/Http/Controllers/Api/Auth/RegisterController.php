<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterOnboardingRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __invoke(RegisterOnboardingRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $fullName = trim(sprintf('%s %s', $payload['first_name'], $payload['last_name']));

        $user = User::create([
            'name' => $fullName !== '' ? $fullName : 'User',
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
        ]);

        $roleName = $payload['role'] === 'babysitter'
            ? env('BABYSITTER_ROLE_NAME', 'Babysitter')
            : env('PARENT_ROLE_NAME', 'Parent');
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->assignRole($role);

        if ($payload['role'] === 'babysitter') {
            $user->babysitterProfile()->create([
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
            ]);
        } else {
            $user->parentProfile()->create([
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
            ]);
        }

        event(new Registered($user));
        $user->notify(new WelcomeNotification($user));

        $deviceName = (string) $request->input('device_name', 'mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->loadMissing(['address', 'parentProfile', 'babysitterProfile', 'media']);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }
}
