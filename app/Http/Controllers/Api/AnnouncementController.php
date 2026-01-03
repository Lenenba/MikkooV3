<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementApplicationResource;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\AnnouncementApplication;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AnnouncementMatchNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->isBabysitter()) {
            return $this->indexForBabysitter($user);
        }

        if (! $user->isParent() && ! $user->isAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
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

        $items = $itemsQuery->get();

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

        return response()->json([
            'data' => AnnouncementResource::collection($items),
            'suggestions' => $suggestions,
            'children' => $children,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isParent()) {
            return response()->json(['message' => 'Forbidden.'], 403);
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
            return response()->json([
                'message' => __('announcements.errors.select_child'),
                'errors' => ['child_ids' => [__('announcements.errors.select_child')]],
            ], 422);
        }

        $primaryChild = $selectedChildren->first() ?? [];
        $defaultChildName = __('announcements.child.default_name');
        $childName = trim((string) ($primaryChild['name'] ?? $defaultChildName));
        $childName = $childName !== '' ? $childName : $defaultChildName;
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

        return response()->json([
            'announcement' => new AnnouncementResource($announcement),
        ], 201);
    }

    public function show(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($user->isAdmin()) {
            // Super admins can view all announcements.
        } elseif ($user->isParent()) {
            if ((int) $announcement->parent_id !== (int) $user->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        } elseif ($user->isBabysitter()) {
            $hasApplication = AnnouncementApplication::query()
                ->where('announcement_id', $announcement->id)
                ->where('babysitter_id', $user->id)
                ->exists();

            if ($announcement->status !== 'open' && ! $hasApplication) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            $serviceNames = Service::query()
                ->where('user_id', $user->id)
                ->pluck('name')
                ->map(fn($name) => trim((string) $name))
                ->filter()
                ->values();

            if (! $hasApplication && $serviceNames->isEmpty()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            $announcementServices = $announcement->resolveServices();
            $matches = $hasApplication ? true : $this->matchesAllServices($announcementServices, $serviceNames->all());

            if (! $matches) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        } else {
            return response()->json(['message' => 'Forbidden.'], 403);
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
                ->get();
        }

        if ($user->isBabysitter()) {
            $myApplication = AnnouncementApplication::query()
                ->where('announcement_id', $announcement->id)
                ->where('babysitter_id', $user->id)
                ->with(['reservation.details', 'babysitter'])
                ->first();
            $myApplicationPayload = $myApplication;
        }

        return response()->json([
            'announcement' => new AnnouncementResource($announcement),
            'viewer_role' => $user->isBabysitter() ? 'Babysitter' : ($user->isAdmin() ? 'SuperAdmin' : 'Parent'),
            'applications' => $applicationsPayload ? AnnouncementApplicationResource::collection($applicationsPayload) : null,
            'my_application' => $myApplicationPayload ? new AnnouncementApplicationResource($myApplicationPayload) : null,
        ]);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isParent()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        $announcement->update([
            'status' => $data['status'],
        ]);

        return response()->json([
            'announcement' => new AnnouncementResource($announcement),
        ]);
    }

    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->isParent()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $announcement->delete();

        return response()->json(null, 204);
    }

    protected function indexForBabysitter(User $user): JsonResponse
    {
        $serviceNames = Service::query()
            ->where('user_id', $user->id)
            ->pluck('name')
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->values();

        if ($serviceNames->isEmpty()) {
            return response()->json([
                'data' => [],
                'suggestions' => [],
            ]);
        }

        $items = Announcement::query()
            ->with(['parent.address'])
            ->where('status', 'open')
            ->latest()
            ->limit(50)
            ->get()
            ->filter(fn(Announcement $announcement) => $this->announcementMatchesServices($announcement, $serviceNames->all()))
            ->take(12)
            ->values();

        return response()->json([
            'data' => AnnouncementResource::collection($items),
            'suggestions' => [],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getParentChildren(User $user): array
    {
        $children = $user->parentProfile?->settings['children'] ?? [];
        if (! is_array($children)) {
            return [];
        }

        return collect($children)
            ->map(function ($child) {
                if (! is_array($child)) {
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
                if (! $child) {
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
     * @param array<int, string> $serviceNames
     */
    protected function announcementMatchesServices(Announcement $announcement, array $serviceNames): bool
    {
        $announcementServices = $announcement->resolveServices();
        if (empty($announcementServices) || empty($serviceNames)) {
            return false;
        }

        foreach ($announcementServices as $announcementService) {
            $matched = false;
            foreach ($serviceNames as $serviceName) {
                $left = strtolower(trim((string) $announcementService));
                $right = strtolower(trim((string) $serviceName));
                if ($left !== '' && $right !== '' && (str_contains($left, $right) || str_contains($right, $left))) {
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

        $babysitters = User::babysitters()
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
}
