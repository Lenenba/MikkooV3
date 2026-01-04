<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationMediaController extends Controller
{
    public function index(Reservation $reservation): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $this->canAccess($user, $reservation)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $media = $reservation->media()
            ->where('collection_name', 'reservation')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'media' => MediaResource::collection($media),
        ]);
    }

    public function store(Request $request, Reservation $reservation): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $this->canAccess($user, $reservation)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $reservation->loadMissing('details');
        $status = $reservation->details?->status;
        if (! in_array($status, ['in_progress', 'completed'], true)) {
            return response()->json([
                'message' => 'Reservation not eligible for gallery upload.',
            ], 422);
        }

        $request->validate([
            'media' => ['required', 'array', 'min:1'],
            'media.*' => ['required', 'file', 'mimes:jpg,png,jpeg,webp,mp4', 'max:25600'],
        ]);

        $files = $request->file('media', []);
        $incomingCount = is_array($files) ? count($files) : 0;
        $existingCount = $reservation->media()
            ->where('collection_name', 'reservation')
            ->count();

        if ($existingCount + $incomingCount > 10) {
            return response()->json([
                'message' => 'Media limit reached.',
            ], 422);
        }

        $created = [];
        foreach ($files as $file) {
            $path = $file->store('reservation-media', 'public');
            $created[] = $reservation->media()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'is_profile_picture' => false,
                'collection_name' => 'reservation',
            ]);
        }

        return response()->json([
            'media' => MediaResource::collection(collect($created)),
        ], 201);
    }

    protected function canAccess(User $user, Reservation $reservation): bool
    {
        if ($user->isParent()) {
            return (int) $reservation->parent_id === (int) $user->id;
        }

        if ($user->isBabysitter()) {
            return (int) $reservation->babysitter_id === (int) $user->id;
        }

        return false;
    }
}
