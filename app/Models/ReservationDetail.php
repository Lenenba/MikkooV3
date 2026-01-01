<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationDetail extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'reservation_details';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'schedule_type',
        'recurrence_frequency',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_end_date',
    ];

    /**
     * Les attributs qui devraient être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recurrence_days' => 'array',
        'recurrence_end_date' => 'date',
    ];

    /**
     * Obtient la réservation parente de ce détail.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
