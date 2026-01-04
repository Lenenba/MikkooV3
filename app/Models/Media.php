<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Media model.
 *
 * @package App\Models
 */
class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory,  SoftDeletes;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'custom_properties' => 'array',
        'is_profile_picture' => 'boolean',
    ];


    /**
     * The attributes that are NOT mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [
        'id',
        'mediaable_id',
        'mediaable_type',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the owning model (User, BabysittingSession, etc.).
     *
     * @return MorphTo
     */
    public function mediaable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the URL of the photo.
     *
     * @return string
     */
    public function scopeIsProfilePicture($query)
    {
        return $query->where('is_profile_picture', true);
    }
}
