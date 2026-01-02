<?php

namespace App\Notifications;

use App\Models\AnnouncementApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AnnouncementApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected AnnouncementApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $application = $this->application->loadMissing([
            'announcement.parent.parentProfile',
            'babysitter.babysitterProfile',
        ]);

        $announcement = $application->announcement;
        $parentName = $this->resolveParentName($announcement?->parent);
        $babysitterName = $this->resolveBabysitterName($application->babysitter);

        return (new MailMessage)
            ->subject(__('notifications.announcement.application_received_subject'))
            ->markdown('emails.announcements.application-received', [
                'announcement' => $announcement,
                'application' => $application,
                'parentName' => $parentName,
                'babysitterName' => $babysitterName,
            ]);
    }

    protected function resolveParentName(?User $user): string
    {
        $fallback = __('common.roles.parent');

        if (! $user) {
            return $fallback;
        }

        $profile = $user->parentProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : __('common.roles.parent');
    }

    protected function resolveBabysitterName(?User $user): string
    {
        $fallback = __('common.roles.babysitter');

        if (! $user) {
            return $fallback;
        }

        $profile = $user->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : __('common.roles.babysitter');
    }
}
