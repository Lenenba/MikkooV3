<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $media = $user->media()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'media' => MediaResource::collection($media),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'collection_name' => ['required', 'string', 'max:255'],
            'images.*' => ['required', 'mimes:jpg,png,jpeg,webp', 'max:5000'],
        ], [
            'images.*.mimes' => __('media.validation.mimes'),
            'images.*.max' => __('media.validation.max'),
        ]);

        $created = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('images', 'public');
                $created[] = $user->media()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'is_profile_picture' => false,
                    'collection_name' => $request->input('collection_name'),
                ]);
            }
        }

        return response()->json([
            'media' => MediaResource::collection(collect($created)),
        ], 201);
    }
}
