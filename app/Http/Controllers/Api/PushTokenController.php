<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:30'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $tokenValue = trim((string) $payload['token']);
        $platform = isset($payload['platform']) ? trim((string) $payload['platform']) : null;
        $deviceName = isset($payload['device_name']) ? trim((string) $payload['device_name']) : null;

        $token = PushToken::query()->updateOrCreate(
            ['token' => $tokenValue],
            [
                'user_id' => $user->id,
                'platform' => $platform ?: null,
                'device_name' => $deviceName ?: null,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'token' => [
                'id' => $token->id,
                'token' => $token->token,
                'platform' => $token->platform,
                'device_name' => $token->device_name,
            ],
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $tokenValue = trim((string) $payload['token']);

        PushToken::query()
            ->where('user_id', $user->id)
            ->where('token', $tokenValue)
            ->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
