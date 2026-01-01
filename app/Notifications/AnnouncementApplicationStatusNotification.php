<?php

namespace App\Notifications;

use App\Models\AnnouncementApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AnnouncementApplicationStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected AnnouncementApplication $application,
        protected string $status
    ) {
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
        $babysitterName = $this->resolveBabysitterName(
            $application->babysitter instanceof User ? $application->babysitter : null
        );
        $parentName = $this->resolveParentName($announcement?->parent);
        $statusKey = strtolower(trim($this->status));

        $subject = match ($statusKey) {
            'accepted' => 'Candidature acceptee',
            'rejected' => 'Candidature refusee',
            'expired' => 'Candidature expiree',
            'withdrawn' => 'Candidature retiree',
            default => 'Mise a jour de candidature',
        };

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.announcements.application-status', [
                'announcement' => $announcement,
                'application' => $application,
                'status' => $statusKey,
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
