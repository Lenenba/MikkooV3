<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'created_at' => $this->created_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'reservation_id' => $this->reservation_id,
            'reservation_status' => $this->reservation?->details?->status,
            'babysitter' => $this->babysitter ? UserResource::make($this->babysitter) : null,
        ];
    }
}
