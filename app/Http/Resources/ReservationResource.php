<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $services = null;
        if ($this->relationLoaded('services')) {
            $services = $this->services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'pivot' => [
                        'quantity' => (int) ($service->pivot->quantity ?? 0),
                        'total' => $service->pivot->total ?? null,
                    ],
                ];
            });
        }

        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->details?->status,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'parent' => UserResource::make($this->whenLoaded('parent')),
            'babysitter' => UserResource::make($this->whenLoaded('babysitter')),
            'services' => $services,
            'details' => ReservationDetailResource::make($this->whenLoaded('details')),
        ];
    }
}
