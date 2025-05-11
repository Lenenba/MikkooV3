<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'babysitter_id',
        'total_amount',
        'notes',
    ];

    /**
     * Les attributs qui devraient être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Obtient le parent qui a fait la réservation.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Obtient la babysitter assignée à la réservation.
     *
     * @return BelongsTo
     */
    public function babysitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'babysitter_id');
    }

    /**
     * Obtient les lignes de service associées à cette réservation.
     * C'est la relation directe avec le modèle de la table pivot.
     *
     * @return HasMany
     */
    public function reservationServices(): HasMany
    {
        return $this->hasMany(ReservationService::class);
    }

    /**
     * Obtient les services associés à cette réservation via la table pivot.
     *
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'reservation_services')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    /**
     * Obtient les détails (date, heure, statut) de la réservation.
     * Une réservation peut avoir plusieurs entrées de détails si cela représente des créneaux ou une historisation.
     *
     * @return HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(ReservationDetail::class);
    }
}
