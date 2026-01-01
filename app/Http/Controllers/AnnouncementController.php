<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Service;
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
            'child_name' => ['required', 'string', 'max:120'],
            'child_age' => ['nullable', 'string', 'max:40'],
            'child_notes' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $childAge = isset($data['child_age']) ? trim((string) $data['child_age']) : null;
        $childAge = $childAge !== '' ? $childAge : null;

        $childNotes = isset($data['child_notes']) ? trim((string) $data['child_notes']) : null;
        $childNotes = $childNotes !== '' ? $childNotes : null;

        $description = isset($data['description']) ? trim((string) $data['description']) : null;
        $description = $description !== '' ? $description : null;

        Announcement::create([
            'parent_id' => $user->id,
            'title' => trim($data['title']),
            'service' => trim($data['service']),
            'child_name' => trim($data['child_name']),
            'child_age' => $childAge,
            'child_notes' => $childNotes,
            'description' => $description,
            'status' => 'open',
        ]);

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

    protected function serviceMatches(?string $announcementService, string $serviceName): bool
    {
        $left = strtolower(trim((string) $announcementService));
        $right = strtolower(trim($serviceName));

        if ($left === '' || $right === '') {
            return false;
        }

        return str_contains($left, $right) || str_contains($right, $left);
    }
}
