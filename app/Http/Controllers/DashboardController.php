<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Services\ReservationStatsService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(ReservationStatsService $statsService)
    {
        $user = Auth::user();
        $role = $this->resolveRole($user);
        $stats = $statsService->getAllStats();

        $upcomingCount = Reservation::forUser($user)
            ->whereHas('details', function ($query) {
                $query
                    ->whereDate('date', '>=', now()->toDateString())
                    ->whereIn('status', ['pending', 'confirmed']);
            })
            ->count();

        return Inertia::render('Dashboard', [
            'dashboard' => [
                'role' => $role,
                'stats' => $stats,
                'kpis' => $this->buildKpis($role, $stats, $upcomingCount),
            ],
            'announcements' => $this->buildAnnouncements($user, $role),
        ]);
    }

    protected function resolveRole(?User $user): string
    {
        if (!$user) {
            return 'Guest';
        }

        if ($user->isAdmin()) {
            return 'Admin';
        }

        return $user->isBabysitter() ? 'Babysitter' : 'Parent';
    }

    /**
     * @param array<string, mixed> $stats
     * @return array<int, array<string, mixed>>
     */
    protected function buildKpis(string $role, array $stats, int $upcomingCount): array
    {
        $totalCount = (int) ($stats['total_count'] ?? 0);
        $totalRevenue = (float) ($stats['total_revenue'] ?? 0);
        $countChange = $stats['count_change_pct'] ?? null;
        $revenueChange = $stats['revenue_change_pct'] ?? null;
        $canceledCount = (int) ($stats['total_canceled_count'] ?? 0);

        if ($role === 'Admin') {
            return [
                [
                    'key' => 'total_revenue',
                    'label' => 'Total Revenue',
                    'value' => $totalRevenue,
                    'format' => 'currency',
                    'change_pct' => $revenueChange,
                    'period' => 'vs last month',
                ],
                [
                    'key' => 'total_reservations',
                    'label' => 'Total Reservations',
                    'value' => $totalCount,
                    'format' => 'number',
                    'change_pct' => $countChange,
                    'period' => 'vs last month',
                ],
                [
                    'key' => 'active_babysitters',
                    'label' => 'Active Babysitters',
                    'value' => User::babysitters()->count(),
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'all time',
                ],
                [
                    'key' => 'active_parents',
                    'label' => 'Active Parents',
                    'value' => User::parents()->count(),
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'all time',
                ],
            ];
        }

        if ($role === 'Babysitter') {
            return [
                [
                    'key' => 'total_jobs',
                    'label' => 'Total Jobs',
                    'value' => $totalCount,
                    'format' => 'number',
                    'change_pct' => $countChange,
                    'period' => 'vs last month',
                ],
                [
                    'key' => 'total_earnings',
                    'label' => 'Total Earnings',
                    'value' => $totalRevenue,
                    'format' => 'currency',
                    'change_pct' => $revenueChange,
                    'period' => 'vs last month',
                ],
                [
                    'key' => 'upcoming_jobs',
                    'label' => 'Upcoming Jobs',
                    'value' => $upcomingCount,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'scheduled',
                ],
                [
                    'key' => 'canceled_jobs',
                    'label' => 'Canceled Jobs',
                    'value' => $canceledCount,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'all time',
                ],
            ];
        }

        return [
            [
                'key' => 'total_reservations',
                'label' => 'Total Reservations',
                'value' => $totalCount,
                'format' => 'number',
                'change_pct' => $countChange,
                'period' => 'vs last month',
            ],
            [
                'key' => 'total_spend',
                'label' => 'Total Spend',
                'value' => $totalRevenue,
                'format' => 'currency',
                'change_pct' => $revenueChange,
                'period' => 'vs last month',
            ],
            [
                'key' => 'upcoming_reservations',
                'label' => 'Upcoming',
                'value' => $upcomingCount,
                'format' => 'number',
                'change_pct' => null,
                'period' => 'scheduled',
            ],
            [
                'key' => 'canceled_reservations',
                'label' => 'Canceled',
                'value' => $canceledCount,
                'format' => 'number',
                'change_pct' => null,
                'period' => 'all time',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildAnnouncements(?User $user, string $role): array
    {
        if (!$user) {
            return ['items' => [], 'suggestions' => []];
        }

        if ($role === 'Parent') {
            $items = Announcement::query()
                ->where('parent_id', $user->id)
                ->withCount([
                    'applications',
                    'applications as pending_applications_count' => fn($query) => $query->where('status', 'pending'),
                ])
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

            return [
                'items' => $items,
                'suggestions' => $suggestions,
            ];
        }

        if ($role === 'Babysitter') {
            $serviceNames = Service::query()
                ->where('user_id', $user->id)
                ->pluck('name')
                ->map(fn($name) => trim((string) $name))
                ->filter()
                ->values();

            if ($serviceNames->isEmpty()) {
                return ['items' => [], 'suggestions' => []];
            }

            $items = Announcement::query()
                ->with(['parent.address'])
                ->where('status', 'open')
                ->latest()
                ->limit(50)
                ->get()
                ->filter(fn(Announcement $announcement) => $this->announcementMatchesServices($announcement, $serviceNames->all()))
                ->take(12)
                ->values()
                ->map(fn(Announcement $announcement) => $this->formatAnnouncement($announcement, true))
                ->values();

            return [
                'items' => $items,
                'suggestions' => [],
            ];
        }

        return ['items' => [], 'suggestions' => []];
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
}
