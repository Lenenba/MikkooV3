<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReservationStatsService
{
    protected Carbon $now;

    public function __construct(protected User $user)
    {
        Carbon::setLocale('fr');
        $this->now = Carbon::now();
    }

    /**
     * Return revenue & count for a given month offset.
     *
     * @param  int|null  $monthOffset  0 => current month, 1 => previous month, null => all time
     * @return array{revenue: float, count: int}
     */
    protected function statsForMonth(?int $monthOffset = null): array
    {
        // English comment: start with the base query (for this user, only confirmed)
        $query = Reservation::forUser(Auth::user())
            ->confirmed();

        if ($monthOffset !== null) {
            // English comment: calculate the start and end of the target month
            $periodStart = $this->now->copy()
                ->subMonths($monthOffset)
                ->startOfMonth();
            $periodEnd = $this->now->copy()
                ->subMonths($monthOffset)
                ->endOfMonth();

            // English comment: apply date filtering
            $query->createdBetween($periodStart, $periodEnd);
        }

        // English comment: aggregate revenue and count
        $revenue = (float) $query->sum('total_amount');
        $count   = (int)   $query->count();

        return compact('revenue', 'count');
    }

    /**
     * Compute all the stats needed by the controller.
     */
    public function getAllStats(): array
    {
        $baseQuery = Reservation::forUser(Auth::user());
        $current  = $this->statsForMonth(0);
        $previous = $this->statsForMonth(1);

        $pendingCount = (clone $baseQuery)
            ->whereHas('details', fn($q) => $q->where('status', 'pending'))
            ->count();

        $confirmedCount = (clone $baseQuery)
            ->whereHas('details', fn($q) => $q->where('status', 'confirmed'))
            ->count();

        $countCanceled = (clone $baseQuery)
            ->canceled()
            ->count();

        $totalRequests = (clone $baseQuery)->count();

        $upcomingCount = (clone $baseQuery)
            ->whereHas('details', function ($query) {
                $query
                    ->whereDate('date', '>=', $this->now->toDateString())
                    ->whereIn('status', ['pending', 'confirmed']);
            })
            ->count();

        $uniqueBabysitters = (clone $baseQuery)
            ->distinct('babysitter_id')
            ->count('babysitter_id');

        $uniqueParents = (clone $baseQuery)
            ->distinct('parent_id')
            ->count('parent_id');

        $revenue = $this->statsForMonth();

        // % change helpers
        $revenueChange = $previous['revenue'] > 0
            ? round((($current['revenue'] - $previous['revenue']) / $previous['revenue']) * 100, 1)
            : null;

        $countChange = $previous['count'] > 0
            ? round((($current['count'] - $previous['count']) / $previous['count']) * 100, 1)
            : null;

        return [
            'current_month_revenue'  => $current['revenue'],
            'previous_month_revenue' => $previous['revenue'],
            'revenue_change_pct'     => $revenueChange,
            'current_month_count'    => $current['count'],
            'previous_month_count'   => $previous['count'],
            'count_change_pct'       => $countChange,
            'avg_revenue_per_booking' => $current['count'] > 0
                ? round($current['revenue'] / $current['count'], 2)
                : null,
            'total_revenue'         => $revenue['revenue'],
            'total_count'           => $revenue['count'],
            'total_requests_count'  => $totalRequests,
            'pending_count'         => $pendingCount,
            'confirmed_count'       => $confirmedCount,
            'total_canceled_count'  => $countCanceled,
            'upcoming_count'        => $upcomingCount,
            'unique_babysitters_count' => $uniqueBabysitters,
            'unique_parents_count'  => $uniqueParents,
            // vous pouvez ajouter ici d'autres métriques…
        ];
    }
}
