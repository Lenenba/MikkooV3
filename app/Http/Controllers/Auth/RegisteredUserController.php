<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterOnboardingRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('onboarding/Index', [
            'step' => 1,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterOnboardingRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $fullName = trim(sprintf('%s %s', $payload['first_name'], $payload['last_name']));
        $user = User::create([
            'name' => $fullName ?: 'User',
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

        Auth::login($user);

        return to_route('onboarding.index', ['step' => 2]);
    }
}
