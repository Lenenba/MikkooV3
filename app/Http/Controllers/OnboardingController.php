<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingAvailabilityRequest;
use App\Http\Requests\OnboardingProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $step = (int) $request->query('step', 0);

        if (! $user) {
            $step = 1;
        } elseif ($step < 1) {
            $step = $user->address()->exists() ? 3 : 2;
        }

        $step = max(1, min(6, $step));

        $role = null;
        $profile = null;
        $account = null;
        $address = null;

        if ($user) {
            $role = $user->isBabysitter() ? 'babysitter' : 'parent';
            $profile = $user->isBabysitter()
                ? $user->babysitterProfile
                : $user->parentProfile;
            $address = $user->address;
            $account = [
                'first_name' => $profile?->first_name,
                'last_name' => $profile?->last_name,
                'email' => $user->email,
            ];
        }

        return Inertia::render('onboarding/Index', [
            'step' => $step,
            'role' => $role,
            'account' => $account,
            'address' => $address,
            'profile' => $profile,
        ]);
    }

    public function storeProfile(OnboardingProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $payload = $request->validated();

        if ($user->isBabysitter()) {
            $profile = $user->babysitterProfile()->firstOrCreate([], $this->profileNameDefaults($user));
            $profile->fill([
                'bio' => $payload['bio'] ?? null,
                'experience' => $payload['experience'] ?? null,
                'price_per_hour' => $payload['price_per_hour'] ?? $profile->price_per_hour ?? 0,
                'payment_frequency' => $payload['payment_frequency'] ?? $profile->payment_frequency ?? 'per_task',
            ]);

            $settings = $profile->settings ?? [];
            if (array_key_exists('services', $payload)) {
                $settings['services'] = $payload['services'];
            }
            $profile->settings = $settings;
            $profile->save();
        } else {
            $profile = $user->parentProfile()->firstOrCreate([], $this->profileNameDefaults($user));
            $settings = $profile->settings ?? [];
            foreach (['children_count', 'children_ages', 'preferences'] as $key) {
                if (array_key_exists($key, $payload)) {
                    $settings[$key] = $payload[$key];
                }
            }
            $profile->settings = $settings;
            $profile->save();
        }

        return to_route('onboarding.index', ['step' => 4]);
    }

    public function storeAvailability(OnboardingAvailabilityRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $payload = $request->validated();
        $profile = $user->isBabysitter()
            ? $user->babysitterProfile
            : $user->parentProfile;

        if (! $profile) {
            $profile = $user->isBabysitter()
                ? $user->babysitterProfile()->create($this->profileNameDefaults($user))
                : $user->parentProfile()->create($this->profileNameDefaults($user));
        }

        $settings = $profile->settings ?? [];
        $settings['availability'] = $payload['availability'] ?? null;
        $settings['availability_notes'] = $payload['availability_notes'] ?? null;
        $profile->settings = $settings;
        $profile->save();

        return to_route('onboarding.index', ['step' => 6]);
    }

    public function finish(Request $request): RedirectResponse
    {
        return to_route('search.babysitter');
    }

    protected function profileNameDefaults(User $user): array
    {
        $name = trim((string) $user->name);
        if ($name === '') {
            return ['first_name' => 'User', 'last_name' => ''];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return [
            'first_name' => $parts[0] ?? 'User',
            'last_name' => $parts[1] ?? '',
        ];
    }
}
