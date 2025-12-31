<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ParentProfile model.
 *
 * @package App\Models
 */
class BabysitterProfile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are NOT mass assignable.
     */
    protected $guarded = [
        'id',
        'user_id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * One-to-one polymorphic relation to address.
     *
     * @return MorphOne
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * One-to-one relation to user.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
