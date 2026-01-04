<?php

namespace App\Notifications;

use App\Models\AnnouncementApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\ExpoPushChannel;

class AnnouncementApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected AnnouncementApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', ExpoPushChannel::class];
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

        return (new MailMessage)
            ->subject(__('notifications.announcement.application_submitted_subject'))
            ->markdown('emails.announcements.application-submitted', [
                'announcement' => $announcement,
                'application' => $application,
                'parentName' => $parentName,
                'babysitterName' => $babysitterName,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toExpoPush(object $notifiable): array
    {
        $application = $this->application->loadMissing([
            'announcement.parent.parentProfile',
            'babysitter.babysitterProfile',
        ]);

        return [
            'title' => __('notifications.announcement.application_submitted_subject'),
            'body' => __('emails.announcements.application_submitted.intro'),
            'data' => [
                'type' => 'announcement',
                'id' => $application->announcement?->id,
                'application_id' => $application->id,
                'action' => 'application_submitted',
            ],
        ];
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
