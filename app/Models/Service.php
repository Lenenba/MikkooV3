<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'details',
    ];

    /**
     * Obtient les lignes de réservation de service associées à ce service.
     */
    public function reservationServices(): HasMany
    {
        return $this->hasMany(ReservationService::class);
    }

    /**
     * Obtient les réservations qui incluent ce service.
     */
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_services')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
