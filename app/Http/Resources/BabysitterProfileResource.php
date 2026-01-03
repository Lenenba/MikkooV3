<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BabysitterProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'birthdate' => $this->birthdate,
            'bio' => $this->bio,
            'experience' => $this->experience,
            'price_per_hour' => $this->price_per_hour,
            'payment_frequency' => $this->payment_frequency,
            'settings' => $this->settings,
        ];
    }
}
