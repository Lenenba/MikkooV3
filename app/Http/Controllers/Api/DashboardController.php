<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Services\ReservationStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(ReservationStatsService $statsService): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $role = $this->resolveRole($user);
        $stats = $statsService->getAllStats();

        $upcomingCount = Reservation::forUser($user)
            ->whereHas('details', function ($query) {
                $query
                    ->whereDate('date', '>=', now()->toDateString())
                    ->whereIn('status', ['pending', 'confirmed']);
            })
            ->count();

        return response()->json([
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
        if (! $user) {
            return 'Guest';
        }

        if ($user->isAdmin()) {
            return 'SuperAdmin';
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

        if ($role === 'SuperAdmin' || $role === 'Admin') {
            $pendingCount = (int) ($stats['pending_count'] ?? 0);
            $confirmedCount = (int) ($stats['confirmed_count'] ?? 0);
            $openAnnouncements = (int) Announcement::query()
                ->where('status', 'open')
                ->count();
            $issuedInvoices = (int) Invoice::query()
                ->where('status', 'issued')
                ->count();

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
                    'key' => 'pending_reservations',
                    'label' => 'Pending Reservations',
                    'value' => $pendingCount,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'pending',
                ],
                [
                    'key' => 'confirmed_reservations',
                    'label' => 'Confirmed Reservations',
                    'value' => $confirmedCount,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'confirmed',
                ],
                [
                    'key' => 'canceled_reservations',
                    'label' => 'Canceled Reservations',
                    'value' => $canceledCount,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'canceled',
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
                [
                    'key' => 'open_announcements',
                    'label' => 'Open Announcements',
                    'value' => $openAnnouncements,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'open',
                ],
                [
                    'key' => 'issued_invoices',
                    'label' => 'Issued Invoices',
                    'value' => $issuedInvoices,
                    'format' => 'number',
                    'change_pct' => null,
                    'period' => 'issued',
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
        if (! $user) {
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
                ->get();

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
                'items' => AnnouncementResource::collection($items),
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
                ->values();

            return [
                'items' => AnnouncementResource::collection($items),
                'suggestions' => [],
            ];
        }

        return ['items' => [], 'suggestions' => []];
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
