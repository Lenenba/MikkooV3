<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementApplication;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AnnouncementApplicationReceivedNotification;
use App\Notifications\AnnouncementApplicationStatusNotification;
use App\Notifications\AnnouncementApplicationSubmittedNotification;
use App\Support\Billing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementApplicationController extends Controller
{
    public function store(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (! $user || ! $user->isBabysitter()) {
            abort(403);
        }

        if ($announcement->status !== 'open') {
            return back()->withErrors([
                'application' => __('announcements.application_errors.not_open'),
            ]);
        }

        if (! $this->babysitterMatchesAnnouncement($announcement, $user)) {
            abort(403);
        }

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $message = isset($data['message']) ? trim((string) $data['message']) : null;
        $message = $message !== '' ? $message : null;

        if (AnnouncementApplication::query()
            ->where('announcement_id', $announcement->id)
            ->where('babysitter_id', $user->id)
            ->exists()) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.already_applied'),
            ]);
        }

        $schedule = $this->resolveAnnouncementSchedule($announcement);
        if (! $schedule['start_date'] || ! $schedule['start_time'] || ! $schedule['end_time']) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.not_scheduled'),
            ]);
        }

        if (! $this->canBabysitterApply($user->id, $schedule)) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.slot_taken'),
            ]);
        }

        $matchedServices = $this->resolveBabysitterServices($announcement, $user);
        if (empty($matchedServices)) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.no_matching_service'),
            ]);
        }

        $quantity = $this->resolveServiceQuantity($announcement);
        $linesPayload = $this->buildServiceLines($matchedServices, $quantity);
        $subtotal = $linesPayload['subtotal'];
        $taxRate = Billing::vatRateForCountry($user->address?->country);
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $expiryHours = (int) config('announcements.application_expiry_hours', 24);
        $expiresAt = $expiryHours > 0 ? now()->addHours($expiryHours) : null;

        $application = DB::transaction(function () use ($announcement, $user, $message, $schedule, $expiresAt, $linesPayload, $totalAmount) {
            $reservation = Reservation::create([
                'parent_id' => $announcement->parent_id,
                'babysitter_id' => $user->id,
                'announcement_id' => $announcement->id,
                'total_amount' => $totalAmount,
                'notes' => $message,
            ]);

            foreach ($linesPayload['lines'] as $line) {
                $reservation->services()->attach($line['service_id'], [
                    'quantity' => $line['quantity'],
                    'total' => $line['total'],
                ]);
            }

            $reservation->details()->create([
                'date' => $schedule['start_date'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'status' => 'pending',
                'schedule_type' => $schedule['schedule_type'],
                'recurrence_frequency' => $schedule['schedule_type'] === 'recurring' ? $schedule['recurrence_frequency'] : null,
                'recurrence_interval' => $schedule['schedule_type'] === 'recurring' ? $schedule['recurrence_interval'] : null,
                'recurrence_days' => $schedule['schedule_type'] === 'recurring' ? $schedule['recurrence_days'] : null,
                'recurrence_end_date' => $schedule['schedule_type'] === 'recurring' ? $schedule['recurrence_end_date'] : null,
            ]);

            return AnnouncementApplication::create([
                'announcement_id' => $announcement->id,
                'babysitter_id' => $user->id,
                'reservation_id' => $reservation->id,
                'status' => AnnouncementApplication::STATUS_PENDING,
                'message' => $message,
                'expires_at' => $expiresAt,
            ]);
        });

        $application->loadMissing(['announcement.parent', 'babysitter']);

        try {
            $application->announcement?->parent?->notify(
                new AnnouncementApplicationReceivedNotification($application)
            );
        } catch (\Throwable $exception) {
            Log::warning('Announcement application parent notification failed.', [
                'announcement_id' => $announcement->id,
                'babysitter_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $user->notify(new AnnouncementApplicationSubmittedNotification($application));
        } catch (\Throwable $exception) {
            Log::warning('Announcement application babysitter notification failed.', [
                'announcement_id' => $announcement->id,
                'babysitter_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', __('flash.application.submitted'));
    }

    public function accept(Request $request, Announcement $announcement, AnnouncementApplication $application)
    {
        $user = $request->user();
        if (! $user || ! $user->isParent()) {
            abort(403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            abort(403);
        }

        if ((int) $application->announcement_id !== (int) $announcement->id) {
            abort(404);
        }

        if ($announcement->status !== 'open') {
            return back()->withErrors([
                'application' => __('announcements.application_errors.not_open'),
            ]);
        }

        if ($application->status !== AnnouncementApplication::STATUS_PENDING) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.cannot_accept'),
            ]);
        }

        $alreadyAccepted = AnnouncementApplication::query()
            ->where('announcement_id', $announcement->id)
            ->where('status', AnnouncementApplication::STATUS_ACCEPTED)
            ->exists();

        if ($alreadyAccepted) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.already_confirmed'),
            ]);
        }

        $rejectedApplications = DB::transaction(function () use ($announcement, $application) {
            $application->update([
                'status' => AnnouncementApplication::STATUS_ACCEPTED,
                'decided_at' => now(),
            ]);

            if ($application->reservation) {
                if ($application->reservation->services()->count() === 0) {
                    $matchedServices = $this->resolveBabysitterServices($announcement, $application->babysitter);
                    if (! empty($matchedServices)) {
                        $quantity = $this->resolveServiceQuantity($announcement);
                        $linesPayload = $this->buildServiceLines($matchedServices, $quantity);
                        $subtotal = $linesPayload['subtotal'];
                        $taxRate = Billing::vatRateForCountry($application->babysitter?->address?->country);
                        $taxAmount = round($subtotal * $taxRate, 2);
                        $totalAmount = round($subtotal + $taxAmount, 2);
                        foreach ($linesPayload['lines'] as $line) {
                            $application->reservation->services()->attach($line['service_id'], [
                                'quantity' => $line['quantity'],
                                'total' => $line['total'],
                            ]);
                        }
                        if ((float) $application->reservation->total_amount <= 0) {
                            $application->reservation->update([
                                'total_amount' => $totalAmount,
                            ]);
                        }
                    }
                }

                $application->reservation->details()->update([
                    'status' => 'confirmed',
                ]);
            }

            $others = AnnouncementApplication::query()
                ->where('announcement_id', $announcement->id)
                ->where('id', '!=', $application->id)
                ->where('status', AnnouncementApplication::STATUS_PENDING)
                ->get();

            foreach ($others as $other) {
                $other->update([
                    'status' => AnnouncementApplication::STATUS_REJECTED,
                    'decided_at' => now(),
                ]);

                if ($other->reservation) {
                    $other->reservation->details()->update([
                        'status' => 'canceled',
                    ]);
                }
            }

            $announcement->update([
                'status' => 'closed',
            ]);

            return $others;
        });

        $application->loadMissing(['announcement.parent', 'babysitter']);
        $rejectedApplications->loadMissing(['announcement.parent', 'babysitter']);

        try {
            $application->babysitter?->notify(
                new AnnouncementApplicationStatusNotification($application, AnnouncementApplication::STATUS_ACCEPTED)
            );
        } catch (\Throwable $exception) {
            Log::warning('Announcement application accept notification failed.', [
                'announcement_id' => $announcement->id,
                'babysitter_id' => $application->babysitter_id,
                'error' => $exception->getMessage(),
            ]);
        }

        foreach ($rejectedApplications as $rejected) {
            try {
                $rejected->babysitter?->notify(
                    new AnnouncementApplicationStatusNotification($rejected, AnnouncementApplication::STATUS_REJECTED)
                );
            } catch (\Throwable $exception) {
                Log::warning('Announcement application rejection notification failed.', [
                    'announcement_id' => $announcement->id,
                    'babysitter_id' => $rejected->babysitter_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('success', __('flash.application.confirmed'));
    }

    public function reject(Request $request, Announcement $announcement, AnnouncementApplication $application)
    {
        $user = $request->user();
        if (! $user || ! $user->isParent()) {
            abort(403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            abort(403);
        }

        if ((int) $application->announcement_id !== (int) $announcement->id) {
            abort(404);
        }

        if ($application->status !== AnnouncementApplication::STATUS_PENDING) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.cannot_reject'),
            ]);
        }

        $application->update([
            'status' => AnnouncementApplication::STATUS_REJECTED,
            'decided_at' => now(),
        ]);

        if ($application->reservation) {
            $application->reservation->details()->update([
                'status' => 'canceled',
            ]);
        }

        try {
            $application->babysitter?->notify(
                new AnnouncementApplicationStatusNotification($application, AnnouncementApplication::STATUS_REJECTED)
            );
        } catch (\Throwable $exception) {
            Log::warning('Announcement application rejection notification failed.', [
                'announcement_id' => $announcement->id,
                'babysitter_id' => $application->babysitter_id,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', __('flash.application.rejected'));
    }

    public function withdraw(Request $request, Announcement $announcement, AnnouncementApplication $application)
    {
        $user = $request->user();
        if (! $user || ! $user->isBabysitter()) {
            abort(403);
        }

        if ((int) $application->announcement_id !== (int) $announcement->id) {
            abort(404);
        }

        if ((int) $application->babysitter_id !== (int) $user->id) {
            abort(403);
        }

        if ($application->status !== AnnouncementApplication::STATUS_PENDING) {
            return back()->withErrors([
                'application' => __('announcements.application_errors.cannot_withdraw'),
            ]);
        }

        $application->update([
            'status' => AnnouncementApplication::STATUS_WITHDRAWN,
            'decided_at' => now(),
        ]);

        if ($application->reservation) {
            $application->reservation->details()->update([
                'status' => 'canceled',
            ]);
        }

        try {
            $application->babysitter?->notify(
                new AnnouncementApplicationStatusNotification($application, AnnouncementApplication::STATUS_WITHDRAWN)
            );
        } catch (\Throwable $exception) {
            Log::warning('Announcement application withdraw notification failed.', [
                'announcement_id' => $announcement->id,
                'babysitter_id' => $application->babysitter_id,
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', __('flash.application.withdrawn'));
    }

    /**
     * @param array<string, mixed> $schedule
     */
    protected function canBabysitterApply(int $babysitterId, array $schedule): bool
    {
        $startDate = $schedule['start_date'];
        $endDate = $schedule['schedule_type'] === 'recurring' && $schedule['recurrence_end_date']
            ? $schedule['recurrence_end_date']
            : $startDate;

        if (! $startDate || ! $endDate) {
            return false;
        }

        $existingDetails = ReservationDetail::query()
            ->whereHas('reservation', function ($query) use ($babysitterId) {
                $query->where('babysitter_id', $babysitterId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['date', 'start_time', 'end_time']);

        if ($existingDetails->isEmpty()) {
            return true;
        }

        $existingByDate = $existingDetails->groupBy(fn($detail) => (string) $detail->date);
        $occurrenceDates = $this->resolveOccurrenceDates($schedule);

        foreach ($occurrenceDates as $date) {
            $dateKey = (string) $date;
            if (! $existingByDate->has($dateKey)) {
                continue;
            }

            foreach ($existingByDate->get($dateKey, []) as $detail) {
                if ($this->timesOverlap($schedule['start_time'], $schedule['end_time'], $detail->start_time, $detail->end_time)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $schedule
     * @return array<int, string>
     */
    protected function resolveOccurrenceDates(array $schedule): array
    {
        $startDate = $schedule['start_date'];
        $endDate = $schedule['schedule_type'] === 'recurring' && $schedule['recurrence_end_date']
            ? $schedule['recurrence_end_date']
            : $startDate;

        if (! $startDate || ! $endDate) {
            return [];
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($schedule['schedule_type'] !== 'recurring') {
            return [$start->toDateString()];
        }

        $frequency = $schedule['recurrence_frequency'] ?? 'weekly';
        $interval = (int) ($schedule['recurrence_interval'] ?? 1);
        $interval = max(1, $interval);
        $days = collect($schedule['recurrence_days'] ?? [])
            ->map(fn($day) => (int) $day)
            ->filter(fn(int $day) => $day >= 1 && $day <= 7)
            ->values()
            ->all();

        $dates = [];
        $maxOccurrences = 366;

        if ($frequency === 'daily') {
            $cursor = $start->copy();
            while ($cursor->lte($end) && count($dates) < $maxOccurrences) {
                $dates[] = $cursor->toDateString();
                $cursor->addDays($interval);
            }

            return $dates;
        }

        if ($frequency === 'monthly') {
            $cursor = $start->copy();
            $dayOfMonth = (int) $start->day;
            while ($cursor->lte($end) && count($dates) < $maxOccurrences) {
                $candidate = $cursor->copy()->day($dayOfMonth);
                if ($candidate->month === $cursor->month && $candidate->lte($end)) {
                    $dates[] = $candidate->toDateString();
                }
                $cursor->addMonthsNoOverflow($interval)->startOfMonth();
            }

            return $dates;
        }

        $cursor = $start->copy();
        $startWeek = $start->copy()->startOfWeek(Carbon::MONDAY);

        while ($cursor->lte($end) && count($dates) < $maxOccurrences) {
            $weekDiff = (int) floor($startWeek->diffInDays($cursor->copy()->startOfWeek(Carbon::MONDAY)) / 7);
            $weekday = (int) $cursor->isoWeekday();
            $isIntervalWeek = $weekDiff % $interval === 0;

            if ($isIntervalWeek && (empty($days) || in_array($weekday, $days, true))) {
                $dates[] = $cursor->toDateString();
            }

            $cursor->addDay();
        }

        return $dates;
    }

    protected function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startMinutes = $this->toMinutes($startA);
        $endMinutes = $this->toMinutes($endA);
        $otherStart = $this->toMinutes($startB);
        $otherEnd = $this->toMinutes($endB);

        return $startMinutes < $otherEnd && $endMinutes > $otherStart;
    }

    protected function toMinutes(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

        return ($hours * 60) + $minutes;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveAnnouncementSchedule(Announcement $announcement): array
    {
        return [
            'start_date' => $announcement->start_date?->toDateString(),
            'start_time' => $announcement->start_time,
            'end_time' => $announcement->end_time,
            'schedule_type' => $announcement->schedule_type ?? 'single',
            'recurrence_frequency' => $announcement->recurrence_frequency,
            'recurrence_interval' => $announcement->recurrence_interval ?? 1,
            'recurrence_days' => $announcement->recurrence_days ?? [],
            'recurrence_end_date' => $announcement->recurrence_end_date?->toDateString(),
        ];
    }

    protected function babysitterMatchesAnnouncement(Announcement $announcement, User $user): bool
    {
        $serviceNames = Service::query()
            ->where('user_id', $user->id)
            ->pluck('name')
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->values();

        if ($serviceNames->isEmpty()) {
            return false;
        }

        $announcementServices = $announcement->resolveServices();

        return $this->matchesAllServices($announcementServices, $serviceNames->all());
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
     * @return array<int, Service>
     */
    protected function resolveBabysitterServices(Announcement $announcement, ?User $user): array
    {
        if (! $user) {
            return [];
        }

        $announcementServices = $announcement->resolveServices();
        if (empty($announcementServices)) {
            return [];
        }

        $services = Service::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        if ($services->isEmpty()) {
            return [];
        }

        $matched = [];
        foreach ($announcementServices as $announcementService) {
            $match = $services->first(fn($service) => $this->serviceMatches($announcementService, (string) $service->name));
            if (! $match) {
                return [];
            }
            $matched[$match->id] = $match;
        }

        return array_values($matched);
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
     * @param array<int, Service> $services
     * @return array{lines: array<int, array{service_id: int, quantity: int, total: float}>, subtotal: float}
     */
    protected function buildServiceLines(array $services, int $quantity): array
    {
        $lines = [];
        $subtotal = 0.0;

        foreach ($services as $service) {
            $lineTotal = round(((float) $service->price) * $quantity, 2);
            $lines[] = [
                'service_id' => $service->id,
                'quantity' => $quantity,
                'total' => $lineTotal,
            ];
            $subtotal = round($subtotal + $lineTotal, 2);
        }

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
        ];
    }

    protected function resolveServiceQuantity(Announcement $announcement): int
    {
        $children = $announcement->children ?? [];
        $count = is_array($children) ? count($children) : 0;
        if ($count > 0) {
            return $count;
        }

        $hasFallback = trim((string) ($announcement->child_name ?? '')) !== '';

        return $hasFallback ? 1 : 1;
    }
}
