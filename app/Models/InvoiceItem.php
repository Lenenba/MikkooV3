<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'reservation_id',
        'description',
        'service_date',
        'quantity',
        'unit_price',
        'subtotal_amount',
        'vat_rate',
        'tax_amount',
        'total_amount',
    ];

    protected $casts = [
        'service_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'vat_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
