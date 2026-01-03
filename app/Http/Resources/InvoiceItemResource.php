<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'description' => $this->description,
            'service_date' => $this->service_date,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal_amount' => $this->subtotal_amount,
            'vat_rate' => $this->vat_rate,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
        ];
    }
}
