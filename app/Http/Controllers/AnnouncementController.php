<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AnnouncementMatchNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        $items = Announcement::query()
            ->where('parent_id', $user->id)
            ->latest()
            ->get()
            ->map(fn(Announcement $announcement) => $this->formatAnnouncement($announcement))
            ->values();

        $children = $this->getParentChildren($user);

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
            'service' => ['required', 'string', 'max:120'],
            'child_ids' => ['required', 'array', 'min:1'],
            'child_ids.*' => ['integer', 'min:0'],
            'child_notes' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1000'],
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

        $announcement = Announcement::create([
            'parent_id' => $user->id,
            'title' => trim($data['title']),
            'service' => trim($data['service']),
            'children' => $selectedChildren->all(),
            'child_name' => $childName,
            'child_age' => $childAge,
            'child_notes' => $childNotes,
            'description' => $description,
            'status' => 'open',
        ]);

        $this->notifyMatchingBabysitters($announcement);

        return back()->with('success', 'Annonce publiee.');
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

        return back()->with('success', 'Annonce mise a jour.');
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

        return back()->with('success', 'Annonce supprimee.');
    }

    public function show(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if ($user->isParent()) {
            if ((int) $announcement->parent_id !== (int) $user->id) {
                abort(403);
            }
        } elseif ($user->isBabysitter()) {
            if ($announcement->status !== 'open') {
                abort(403);
            }

            $serviceNames = Service::query()
                ->where('user_id', $user->id)
                ->pluck('name')
                ->map(fn($name) => trim((string) $name))
                ->filter()
                ->values();

            if ($serviceNames->isEmpty()) {
                abort(403);
            }

            $matches = $serviceNames->contains(function ($serviceName) use ($announcement) {
                return $this->serviceMatches($announcement->service, (string) $serviceName);
            });

            if (!$matches) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $announcement->load(['parent.address']);

        return Inertia::render('announcements/Show', [
            'announcement' => $this->formatAnnouncement($announcement, true),
            'viewerRole' => $user->isBabysitter() ? 'Babysitter' : 'Parent',
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
            'service' => $announcement->service,
            'children' => $announcement->children ?? [],
            'child_name' => $announcement->child_name,
            'child_age' => $announcement->child_age,
            'child_notes' => $announcement->child_notes,
            'description' => $announcement->description,
            'status' => $announcement->status,
            'created_at' => $announcement->created_at?->toDateTimeString(),
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

    protected function notifyMatchingBabysitters(Announcement $announcement): void
    {
        $service = trim((string) ($announcement->service ?? ''));
        if ($service === '') {
            return;
        }

        $services = Service::query()
            ->whereNotNull('user_id')
            ->get(['user_id', 'name']);

        $matchedUserIds = $services
            ->filter(fn($item) => $this->serviceMatches($service, (string) $item->name))
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($matchedUserIds->isEmpty()) {
            return;
        }

        $babysitters = User::Babysitters()
            ->whereIn('id', $matchedUserIds)
            ->get();

        foreach ($babysitters as $babysitter) {
            $babysitter->notify(new AnnouncementMatchNotification($announcement));
        }
    }
}
