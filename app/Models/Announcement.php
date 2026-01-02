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
        'services',
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
        'services' => 'array',
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

    /**
     * Resolve the list of requested services.
     *
     * @return array<int, string>
     */
    public function resolveServices(): array
    {
        $items = [];
        $services = $this->services;

        if (is_array($services)) {
            $items = $services;
        }

        if (empty($items)) {
            $service = trim((string) ($this->service ?? ''));
            if ($service !== '') {
                $items = preg_split('/[,;]+/', $service) ?: [];
            }
        }

        $normalized = [];
        $seen = [];

        foreach ($items as $item) {
            $label = trim((string) $item);
            if ($label === '') {
                continue;
            }

            $key = strtolower($label);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $label;
        }

        return $normalized;
    }

    /**
     * Build a display label for the requested services.
     */
    public function serviceLabel(): string
    {
        $services = $this->resolveServices();
        if (! empty($services)) {
            return implode(', ', $services);
        }

        $service = trim((string) ($this->service ?? ''));

        return $service !== '' ? $service : '-';
    }
}
