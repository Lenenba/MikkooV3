<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Address;
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
        return $query->when($filters['name'] ?? null, function (Builder $q, string $searchTerm) {
            $q->where(function (Builder $subQuery) use ($searchTerm) {
                // Filtrer par le nom de l'utilisateur
                $subQuery->where('name', 'like', '%' . $searchTerm . '%')
                    // OU filtrer par le prénom ou le nom de famille sur le profil lié
                    ->orWhereHas('babysitterProfile', function (Builder $profileQuery) use ($searchTerm) {
                        $profileQuery->where('first_name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
                    });
            });
        });
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
