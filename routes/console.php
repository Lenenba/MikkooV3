<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\AnnouncementApplication;
use App\Notifications\AnnouncementApplicationStatusNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('announcements:expire-applications', function () {
    $expired = AnnouncementApplication::query()
        ->where('status', AnnouncementApplication::STATUS_PENDING)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->with(['babysitter', 'reservation.details', 'announcement.parent'])
        ->get();

    foreach ($expired as $application) {
        $application->update([
            'status' => AnnouncementApplication::STATUS_EXPIRED,
            'decided_at' => now(),
        ]);

        if ($application->reservation) {
            $application->reservation->details()->update([
                'status' => 'canceled',
            ]);
        }

        try {
            $application->babysitter?->notify(
                new AnnouncementApplicationStatusNotification($application, AnnouncementApplication::STATUS_EXPIRED)
            );
        } catch (\Throwable $exception) {
            Log::warning('Announcement application expiration notification failed.', [
                'application_id' => $application->id,
                'babysitter_id' => $application->babysitter_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    $this->info('Expired applications processed: ' . $expired->count());
})->purpose('Expire pending announcement applications');
