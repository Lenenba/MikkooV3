<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\GeneratesSequentialNumber;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reservation extends Model
{
    use HasFactory, SoftDeletes, GeneratesSequentialNumber;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
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
     * Les attributs qui devraient être cachés pour la sérialisation.
     *
     * @var array<int, string>
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate the customer number before creating
        static::creating(function ($reservation) {
            $reservation->number = self::generateNumber($reservation->babysitter_id, 'R');
        });
    }

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
            ->withPivot('quantity')
            ->withTimestamps();
    }
    /**
     * Obtient les détails (date, heure, statut) de la réservation.
     * Une réservation peut avoir plusieurs entrées de détails si cela représente des créneaux ou une historisation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function details(): HasOne
    {
        return $this->hasOne(ReservationDetail::class);
    }

    /**
     * Scope a query to only include customers of a given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByParent($query, int $userId): Builder
    {
        return $query->where('parent_id', $userId);
    }

    /**
     * Scope a query to only include reservations for a given babysitter.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBabysitter($query, int $userId): Builder
    {
        return $query->where('babysitter_id', $userId);
    }

    /**
     * Scope to include only confirmed reservations.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereHas(
            'details',
            fn($q) =>
            $q->where('status', 'confirmed')
        );
    }


    /**
     * Scope to include only canceled reservations.
     */
    public function scopeCanceled(Builder $query): Builder
    {
        return $query->whereHas(
            'details',
            fn($q) =>
            $q->where('status', 'canceled')
        );
    }

    /**
     * Scope a query to only include reservations between two dates.
     *
     * @param Builder $query
     * @param Carbon  $start
     * @param Carbon  $end
     * @return Builder
     */
    public function scopeCreatedBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope the query to only include reservations visible by a given user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User                       $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, User $user): Builder
    {
        return $query
            // If parent → only their bookings
            ->when($user->isParent(), fn($q) => $q->where('parent_id', $user->id))
            // If babysitter → only bookings where they are booked
            ->when($user->isBabysitter(), fn($q) => $q->where('babysitter_id', $user->id))
            // If neither parent nor babysitter and not admin → no records
            ->when(
                !$user->isParent() && !$user->isBabysitter() && !$user->isAdmin(),
                fn($q) => $q->whereRaw('0 = 1')
            );
    }
}
