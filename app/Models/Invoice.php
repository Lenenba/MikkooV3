<?php

namespace App\Models;

use App\Models\Traits\GeneratesSequentialNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, GeneratesSequentialNumber;

    protected $fillable = [
        'number',
        'babysitter_id',
        'parent_id',
        'status',
        'currency',
        'vat_rate',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'period_start',
        'period_end',
        'issued_at',
        'due_at',
        'paid_at',
    ];

    protected $casts = [
        'vat_rate' => 'decimal:4',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $invoice) {
            if (! $invoice->number && $invoice->babysitter_id) {
                $invoice->number = self::generateNumber($invoice->babysitter_id, 'INV');
            }
        });
    }

    public function babysitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'babysitter_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
