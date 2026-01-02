<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementApplication;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AnnouncementMatchNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->isParent() && !$user->isAdmin())) {
            abort(403);
        }

        $itemsQuery = Announcement::query()
            ->withCount([
                'applications',
                'applications as pending_applications_count' => fn($query) => $query->where('status', 'pending'),
            ])
            ->latest();

        if ($user->isParent()) {
            $itemsQuery->where('parent_id', $user->id);
        } else {
            $itemsQuery->with(['parent.address']);
        }

        $items = $itemsQuery
            ->get()
            ->map(fn(Announcement $announcement) => $this->formatAnnouncement($announcement, $user->isAdmin()))
            ->values();

        $children = $user->isParent() ? $this->getParentChildren($user) : [];

        $suggestions = Service::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(50)
            ->pluck('name')
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->values();

        return Inertia::render('announcements/Index', [
            'announcements' => [
                'items' => $items,
                'suggestions' => $suggestions,
                'children' => $children,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'services' => ['nullable', 'array', 'min:1', 'required_without:service'],
            'services.*' => ['string', 'max:120'],
            'service' => ['nullable', 'string', 'max:255', 'required_without:services'],
            'child_ids' => ['required', 'array', 'min:1'],
            'child_ids.*' => ['integer', 'min:0'],
            'child_notes' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:160'],
            'schedule_type' => ['required', 'string', 'in:single,recurring'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'recurrence_frequency' => ['nullable', 'required_if:schedule_type,recurring', 'in:daily,weekly,monthly'],
            'recurrence_interval' => ['nullable', 'integer', 'min:1'],
            'recurrence_days' => ['nullable', 'array', 'required_if:recurrence_frequency,weekly'],
            'recurrence_days.*' => ['integer', 'min:1', 'max:7'],
            'recurrence_end_date' => ['nullable', 'required_if:schedule_type,recurring', 'date', 'after_or_equal:start_date'],
        ]);

        $availableChildren = collect($this->getParentChildren($user))->keyBy('id');
        $selectedChildren = collect($data['child_ids'] ?? [])
            ->unique()
            ->map(fn($id) => $availableChildren->get((int) $id))
            ->filter()
            ->values();

        if ($selectedChildren->isEmpty()) {
            return back()->withErrors([
                'child_ids' => 'Selectionnez au moins un enfant existant.',
            ]);
        }

        $primaryChild = $selectedChildren->first() ?? [];
        $childName = trim((string) ($primaryChild['name'] ?? 'Enfant'));
        $childName = $childName !== '' ? $childName : 'Enfant';
        $childAge = isset($primaryChild['age']) ? trim((string) $primaryChild['age']) : null;
        $childAge = $childAge !== '' ? $childAge : null;

        $childNotes = isset($data['child_notes']) ? trim((string) $data['child_notes']) : null;
        $childNotes = $childNotes !== '' ? $childNotes : null;

        $description = isset($data['description']) ? trim((string) $data['description']) : null;
        $description = $description !== '' ? $description : null;

        $location = isset($data['location']) ? trim((string) $data['location']) : null;
        $location = $location !== '' ? $location : null;

        $scheduleType = $data['schedule_type'] ?? 'single';
        $scheduleType = in_array($scheduleType, ['single', 'recurring'], true) ? $scheduleType : 'single';

        $recurrenceDays = collect($data['recurrence_days'] ?? [])
            ->map(fn($day) => (int) $day)
            ->filter(fn(int $day) => $day >= 1 && $day <= 7)
            ->unique()
            ->values()
            ->all();

        $recurrenceFrequency = $scheduleType === 'recurring'
            ? (isset($data['recurrence_frequency']) ? trim((string) $data['recurrence_frequency']) : null)
            : null;
        $recurrenceFrequency = $recurrenceFrequency !== '' ? $recurrenceFrequency : null;

        $recurrenceInterval = $scheduleType === 'recurring'
            ? (int) ($data['recurrence_interval'] ?? 1)
            : null;

        $recurrenceEndDate = $scheduleType === 'recurring'
            ? ($data['recurrence_end_date'] ?? null)
            : null;

        $services = $this->normalizeServicesInput($data['services'] ?? null, $data['service'] ?? null);
        $serviceLabel = ! empty($services) ? implode(', ', $services) : trim((string) ($data['service'] ?? ''));

        $announcement = Announcement::create([
            'parent_id' => $user->id,
            'title' => trim($data['title']),
            'service' => $serviceLabel,
            'services' => $services,
            'children' => $selectedChildren->all(),
            'child_name' => $childName,
            'child_age' => $childAge,
            'child_notes' => $childNotes,
            'description' => $description,
            'location' => $location,
            'schedule_type' => $scheduleType,
            'start_date' => $data['start_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'recurrence_frequency' => $recurrenceFrequency,
            'recurrence_interval' => $scheduleType === 'recurring' ? $recurrenceInterval : null,
            'recurrence_days' => $scheduleType === 'recurring' ? $recurrenceDays : null,
            'recurrence_end_date' => $scheduleType === 'recurring' ? $recurrenceEndDate : null,
            'status' => 'open',
        ]);

        $this->notifyMatchingBabysitters($announcement);

        return back()->with('success', __('flash.announcement.published'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        $announcement->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', __('flash.announcement.updated'));
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            abort(403);
        }

        $announcement->delete();

        return back()->with('success', __('flash.announcement.deleted'));
    }

    public function show(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            // Super admins can view any announcement.
        } elseif ($user->isParent()) {
            if ((int) $announcement->parent_id !== (int) $user->id) {
                abort(403);
            }
        } elseif ($user->isBabysitter()) {
            $hasApplication = AnnouncementApplication::query()
                ->where('announcement_id', $announcement->id)
                ->where('babysitter_id', $user->id)
                ->exists();

            if ($announcement->status !== 'open' && ! $hasApplication) {
                abort(403);
            }

            $serviceNames = Service::query()
                ->where('user_id', $user->id)
                ->pluck('name')
                ->map(fn($name) => trim((string) $name))
                ->filter()
                ->values();

            if (! $hasApplication && $serviceNames->isEmpty()) {
                abort(403);
            }

            $announcementServices = $announcement->resolveServices();
            $matches = $hasApplication ? true : $this->matchesAllServices($announcementServices, $serviceNames->all());

            if (!$matches) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $announcement->load(['parent.address']);

        $applicationsPayload = null;
        $myApplicationPayload = null;

        if ($user->isParent() || $user->isAdmin()) {
            $applicationsPayload = AnnouncementApplication::query()
                ->where('announcement_id', $announcement->id)
                ->with([
                    'babysitter.address',
                    'babysitter.babysitterProfile',
                    'babysitter.media',
                    'babysitter.receivedRatings',
                    'reservation.details',
                ])
                ->latest()
                ->get()
                ->map(fn(AnnouncementApplication $application) => $this->formatApplication($application))
                ->values();
        }

        if ($user->isBabysitter()) {
            $myApplication = AnnouncementApplication::query()
                ->where('announcement_id', $announcement->id)
                ->where('babysitter_id', $user->id)
                ->with(['reservation.details'])
                ->first();
            $myApplicationPayload = $myApplication ? $this->formatApplication($myApplication) : null;
        }

        return Inertia::render('announcements/Show', [
            'announcement' => $this->formatAnnouncement($announcement, true),
            'viewerRole' => $user->isBabysitter() ? 'Babysitter' : ($user->isAdmin() ? 'SuperAdmin' : 'Parent'),
            'applications' => $applicationsPayload,
            'myApplication' => $myApplicationPayload,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatAnnouncement(Announcement $announcement, bool $includeParent = false): array
    {
        $payload = [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'service' => $announcement->serviceLabel(),
            'services' => $announcement->resolveServices(),
            'children' => $announcement->children ?? [],
            'child_name' => $announcement->child_name,
            'child_age' => $announcement->child_age,
            'child_notes' => $announcement->child_notes,
            'description' => $announcement->description,
            'location' => $announcement->location,
            'start_date' => $announcement->start_date?->toDateString(),
            'start_time' => $announcement->start_time,
            'end_time' => $announcement->end_time,
            'schedule_type' => $announcement->schedule_type,
            'recurrence_frequency' => $announcement->recurrence_frequency,
            'recurrence_interval' => $announcement->recurrence_interval,
            'recurrence_days' => $announcement->recurrence_days ?? [],
            'recurrence_end_date' => $announcement->recurrence_end_date?->toDateString(),
            'status' => $announcement->status,
            'created_at' => $announcement->created_at?->toDateTimeString(),
            'applications_count' => $announcement->applications_count ?? null,
            'pending_applications_count' => $announcement->pending_applications_count ?? null,
        ];

        if ($includeParent) {
            $parent = $announcement->parent;
            $payload['parent'] = $parent
                ? [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'city' => $parent->address?->city,
                ]
                : null;
        }

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getParentChildren(User $user): array
    {
        $children = $user->parentProfile?->settings['children'] ?? [];
        if (!is_array($children)) {
            return [];
        }

        return collect($children)
            ->map(function ($child) {
                if (!is_array($child)) {
                    return null;
                }

                $name = trim((string) ($child['name'] ?? ''));
                $age = $child['age'] ?? null;
                $age = $age !== null ? trim((string) $age) : null;

                return [
                    'name' => $name !== '' ? $name : null,
                    'age' => $age !== '' ? $age : null,
                    'allergies' => isset($child['allergies']) ? trim((string) $child['allergies']) : null,
                    'details' => isset($child['details']) ? trim((string) $child['details']) : null,
                    'photo' => $child['photo'] ?? null,
                ];
            })
            ->filter(function ($child) {
                if (!$child) {
                    return false;
                }
                return $child['name'] !== null
                    || $child['age'] !== null
                    || $child['allergies'] !== null
                    || $child['details'] !== null
                    || $child['photo'] !== null;
            })
            ->values()
            ->map(function (array $child, int $index) {
                return [
                    'id' => $index,
                    'name' => $child['name'],
                    'age' => $child['age'],
                    'allergies' => $child['allergies'],
                    'details' => $child['details'],
                    'photo' => $child['photo'],
                ];
            })
            ->all();
    }

    protected function serviceMatches(?string $announcementService, string $serviceName): bool
    {
        $left = strtolower(trim((string) $announcementService));
        $right = strtolower(trim($serviceName));

        if ($left === '' || $right === '') {
            return false;
        }

        return str_contains($left, $right) || str_contains($right, $left);
    }

    /**
     * @param array<int, string> $announcementServices
     * @param array<int, string> $serviceNames
     */
    protected function matchesAllServices(array $announcementServices, array $serviceNames): bool
    {
        if (empty($announcementServices) || empty($serviceNames)) {
            return false;
        }

        foreach ($announcementServices as $announcementService) {
            $matched = false;
            foreach ($serviceNames as $serviceName) {
                if ($this->serviceMatches($announcementService, (string) $serviceName)) {
                    $matched = true;
                    break;
                }
            }
            if (! $matched) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string>|string|null $services
     * @return array<int, string>
     */
    protected function normalizeServicesInput(array|string|null $services, ?string $fallback = null): array
    {
        $items = [];

        if (is_array($services)) {
            $items = $services;
        } elseif (is_string($services)) {
            $items = preg_split('/[,;]+/', $services) ?: [];
        }

        if (empty($items) && $fallback !== null) {
            $items = preg_split('/[,;]+/', $fallback) ?: [];
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

    protected function notifyMatchingBabysitters(Announcement $announcement): void
    {
        $announcementServices = $announcement->resolveServices();
        if (empty($announcementServices)) {
            return;
        }

        if (! Schema::hasTable('services') || ! Schema::hasColumn('services', 'user_id')) {
            return;
        }

        $services = Service::query()
            ->whereNotNull('user_id')
            ->get(['user_id', 'name']);

        $matchedUserIds = $services
            ->groupBy('user_id')
            ->filter(function ($group) use ($announcementServices) {
                $serviceNames = $group->pluck('name')->map(fn($name) => trim((string) $name))->filter()->values()->all();
                return $this->matchesAllServices($announcementServices, $serviceNames);
            })
            ->keys()
            ->values();

        if ($matchedUserIds->isEmpty()) {
            return;
        }

        $babysitters = User::Babysitters()
            ->whereIn('id', $matchedUserIds)
            ->get();

        foreach ($babysitters as $babysitter) {
            try {
                $babysitter->notify(new AnnouncementMatchNotification($announcement));
            } catch (\Throwable $exception) {
                Log::warning('Announcement notification failed.', [
                    'announcement_id' => $announcement->id,
                    'babysitter_id' => $babysitter->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatApplication(AnnouncementApplication $application): array
    {
        $babysitter = $application->babysitter;
        $profile = $babysitter?->babysitterProfile;
        $fullName = trim((string) (($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')));
        $name = $fullName !== '' ? $fullName : ($babysitter?->name ?? 'Babysitter');
        $media = $babysitter?->media ?? [];
        $profilePicture = $media->firstWhere('is_profile_picture', true)?->file_path
            ?? $media->first()?->file_path;

        $ratingAvg = $babysitter?->rating_avg ?? null;
        $ratingCount = $babysitter?->rating_count ?? null;

        if ($babysitter && $ratingAvg === null) {
            $ratingAvg = $babysitter->relationLoaded('receivedRatings')
                ? $babysitter->receivedRatings->avg('rating')
                : $babysitter->receivedRatings()->avg('rating');
        }

        if ($babysitter && $ratingCount === null) {
            $ratingCount = $babysitter->relationLoaded('receivedRatings')
                ? $babysitter->receivedRatings->count()
                : $babysitter->receivedRatings()->count();
        }

        return [
            'id' => $application->id,
            'status' => $application->status,
            'message' => $application->message,
            'created_at' => $application->created_at?->toDateTimeString(),
            'expires_at' => $application->expires_at?->toDateTimeString(),
            'reservation_id' => $application->reservation_id,
            'reservation_status' => $application->reservation?->details?->status,
            'babysitter' => $babysitter
                ? [
                    'id' => $babysitter->id,
                    'name' => $name,
                    'email' => $babysitter->email,
                    'city' => $babysitter->address?->city,
                    'rating_avg' => $ratingAvg,
                    'rating_count' => $ratingCount,
                    'price_per_hour' => $profile?->price_per_hour,
                    'profile_picture' => $profilePicture,
                ]
                : null,
        ];
    }
}
