<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Address;
use App\Models\Announcement;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * User model.
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * All media items (photos, vidéos, avatars, etc.) attached to the user.
     *
     * @return MorphMany
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    /**
     * One-to-one relation to address.
     *
     * @return MorphOne
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * One-to-one relation to babysitter profile.
     *
     * @return HasOne
     */
    public function babysitterProfile(): HasOne
    {
        return $this->hasOne(BabysitterProfile::class);
    }

    /**
     * Services offered by the babysitter.
     *
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'user_id');
    }

    /**
     * One-to-one relation to parent profile.
     *
     * @return HasOne
     */
    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentProfile::class);
    }

    /**
     * The user's avatar (single media item in the 'avatar' collection).
     *
     * @return MorphOne
     */
    public function avatar(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediaable')
            ->where('collection_name', 'avatar');
    }

    /**
     * The user's profile picture (single media item in the 'profile_picture' collection).
     *
     * @return HasMany
     */
    public function babysitterReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'babysitter_id');
    }

    /**
     * The user's profile picture (single media item in the 'profile_picture' collection).
     *
     * @return HasMany
     */
    public function parentReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'parent_id');
    }

    /**
     * Announcements created by the parent.
     *
     * @return HasMany
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'parent_id');
    }

    /**
     * Ratings received by this user.
     *
     * @return HasMany
     */
    public function receivedRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'reviewee_id');
    }

    /**
     * Ratings authored by this user.
     *
     * @return HasMany
     */
    public function givenRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'reviewer_id');
    }
    /**
     * All media in the 'garde' collection (photos/vidéos de gardes effectuées).
     *
     * @return MorphMany
     */
    public function gardeMedia(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediaable')
            ->where('collection_name', 'garde');
    }

    /**
     * Scope a query to only include parents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParents($query)
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', env('PARENT_ROLE_NAME')));
    }

    /**
     * Scope a query to only include babysitters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBabysitters($query)
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', env('BABYSITTER_ROLE_NAME')));
    }

    /**
     * Scope a query to only include users with a given name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMostRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to filter users by name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|null  $name
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $hasValue = static fn($value) => $value !== null && $value !== '';

        if ($hasValue($filters['name'] ?? null)) {
            $searchTerm = $filters['name'];
            $query->where(function (Builder $subQuery) use ($searchTerm) {
                $like = '%' . $searchTerm . '%';
                $subQuery->where('name', 'like', $like)
                    ->orWhereHas('babysitterProfile', function (Builder $profileQuery) use ($like) {
                        $profileQuery->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like);
                    })
                    ->orWhereHas('address', function (Builder $addressQuery) use ($like) {
                        $addressQuery->where('city', 'like', $like)
                            ->orWhere('country', 'like', $like)
                            ->orWhere('province', 'like', $like);
                    });
            });
        }

        if ($hasValue($filters['city'] ?? null)) {
            $city = $filters['city'];
            $query->where(function (Builder $subQuery) use ($city) {
                $subQuery->whereHas('address', function (Builder $addressQuery) use ($city) {
                    $addressQuery->where('city', 'like', '%' . $city . '%');
                });
            });
        }

        if ($hasValue($filters['country'] ?? null)) {
            $country = $filters['country'];
            $query->where(function (Builder $subQuery) use ($country) {
                $subQuery->whereHas('address', function (Builder $addressQuery) use ($country) {
                    $addressQuery->where('country', 'like', '%' . $country . '%');
                });
            });
        }

        if ($hasValue($filters['min_price'] ?? null)) {
            $minPrice = (float) $filters['min_price'];
            $query->whereHas('babysitterProfile', function (Builder $profileQuery) use ($minPrice) {
                $profileQuery->where('price_per_hour', '>=', $minPrice);
            });
        }

        if ($hasValue($filters['max_price'] ?? null)) {
            $maxPrice = (float) $filters['max_price'];
            $query->whereHas('babysitterProfile', function (Builder $profileQuery) use ($maxPrice) {
                $profileQuery->where('price_per_hour', '<=', $maxPrice);
            });
        }

        if ($hasValue($filters['payment_frequency'] ?? null)) {
            $frequency = $filters['payment_frequency'];
            $query->whereHas('babysitterProfile', function (Builder $profileQuery) use ($frequency) {
                $profileQuery->where('payment_frequency', $frequency);
            });
        }

        if ($hasValue($filters['min_rating'] ?? null)) {
            $minRating = (float) $filters['min_rating'];
            $query->havingRaw('rating_avg >= ?', [$minRating]);
        }

        return $query;
    }

    /**
     * Scope a query to include rating summary columns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRatingSummary(Builder $query): Builder
    {
        return $query
            ->withAvg('receivedRatings as rating_avg', 'rating')
            ->withCount('receivedRatings as rating_count');
    }

    /**
     * Check if the user has a given role name.
     *
     * @param  string  $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Assign a role to the user.
     *
     * @param  mixed  $role  Role instance or role id
     * @return void
     */
    public function assignRole($role): void
    {
        $this->roles()->attach($role);
    }

    /**
     * Determine if the user is a Parent.
     *
     * @return bool
     */
    public function isParent(): bool
    {
        return $this->hasRole(env('PARENT_ROLE_NAME'));
    }

    /**
     * Determine if the user is a Babysitter.
     *
     * @return bool
     */
    public function isBabysitter(): bool
    {
        return $this->hasRole(env('BABYSITTER_ROLE_NAME'));
    }

    /**
     * Determine if the user is an Admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(env('SUPER_ADMIN_ROLE_NAME'));
    }
}
