<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $payload = [
            'id' => $this->id,
            'title' => $this->title,
            'service' => $this->serviceLabel(),
            'services' => $this->resolveServices(),
            'children' => $this->children ?? [],
            'child_name' => $this->child_name,
            'child_age' => $this->child_age,
            'child_notes' => $this->child_notes,
            'description' => $this->description,
            'location' => $this->location,
            'start_date' => $this->start_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'schedule_type' => $this->schedule_type,
            'recurrence_frequency' => $this->recurrence_frequency,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_days' => $this->recurrence_days ?? [],
            'recurrence_end_date' => $this->recurrence_end_date?->toDateString(),
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'applications_count' => $this->applications_count ?? null,
            'pending_applications_count' => $this->pending_applications_count ?? null,
        ];

        if ($this->relationLoaded('parent')) {
            $payload['parent'] = UserResource::make($this->parent);
        }

        return $payload;
    }
}
