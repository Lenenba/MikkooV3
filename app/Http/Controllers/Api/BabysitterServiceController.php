<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BabysitterServiceController extends Controller
{
    public function __invoke(Request $request, User $babysitter): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $babysitter->isBabysitter()) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $canView = $user->isAdmin()
            || $user->isParent()
            || ($user->isBabysitter() && (int) $user->id === (int) $babysitter->id);

        if (! $canView) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $services = Service::query()
            ->where('user_id', $babysitter->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'services' => ServiceResource::collection($services),
        ]);
    }
}
