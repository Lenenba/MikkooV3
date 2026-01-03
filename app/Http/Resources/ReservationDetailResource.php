<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'completed_at' => $this->completed_at?->toISOString(),
            'schedule_type' => $this->schedule_type,
            'recurrence_frequency' => $this->recurrence_frequency,
            'recurrence_interval' => $this->recurrence_interval,
            'recurrence_days' => $this->recurrence_days ?? [],
            'recurrence_end_date' => $this->recurrence_end_date?->toDateString(),
        ];
    }
}
