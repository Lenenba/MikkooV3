<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $path = $this->file_path;
        $url = $path ? Storage::disk('public')->url($path) : null;

        return [
            'id' => $this->id,
            'url' => $url,
            'file_path' => $this->file_path,
            'collection_name' => $this->collection_name,
            'mime_type' => $this->mime_type,
            'is_profile' => (bool) $this->is_profile_picture,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
