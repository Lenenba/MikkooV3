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
            ->subject('Nouvelle candidature recue')
            ->markdown('emails.announcements.application-received', [
                'announcement' => $announcement,
                'application' => $application,
                'parentName' => $parentName,
                'babysitterName' => $babysitterName,
            ]);
    }

    protected function resolveParentName(?User $user): string
    {
        if (! $user) {
            return 'Parent';
        }

        $profile = $user->parentProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : 'Parent';
    }

    protected function resolveBabysitterName(?User $user): string
    {
        if (! $user) {
            return 'Babysitter';
        }

        $profile = $user->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : 'Babysitter';
    }
}
