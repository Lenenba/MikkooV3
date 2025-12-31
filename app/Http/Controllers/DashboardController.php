<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
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
}
