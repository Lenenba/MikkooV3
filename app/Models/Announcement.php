<?php

namespace App\Models;

use App\Models\AnnouncementApplication;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'title',
        'service',
        'children',
        'child_name',
        'child_age',
        'child_notes',
        'description',
        'location',
        'start_date',
        'start_time',
        'end_time',
        'schedule_type',
        'recurrence_frequency',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_end_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'children' => 'array',
        'recurrence_days' => 'array',
        'start_date' => 'date',
        'recurrence_end_date' => 'date',
    ];

    /**
     * Parent user who created the announcement.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Applications submitted by babysitters.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(AnnouncementApplication::class);
    }
}
