<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $role = 'parent';
        if ($this->isAdmin()) {
            $role = 'superadmin';
        } elseif ($this->isBabysitter()) {
            $role = 'babysitter';
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $role,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'address' => AddressResource::make($this->whenLoaded('address')),
            'babysitter_profile' => BabysitterProfileResource::make($this->whenLoaded('babysitterProfile')),
            'parent_profile' => ParentProfileResource::make($this->whenLoaded('parentProfile')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'rating_avg' => $this->rating_avg ?? null,
            'rating_count' => $this->rating_count ?? null,
        ];
    }
}
