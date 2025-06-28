<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationService extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'reservation_services'; // Explicite pour la clarté

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'service_id',
        'quantity',
    ];

    /**
     * Obtient la réservation associée à cette ligne de service.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Obtient le service associé à cette ligne de service.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
