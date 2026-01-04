<?php

namespace App\Notifications;

use App\Models\AnnouncementApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\ExpoPushChannel;

class AnnouncementApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected AnnouncementApplication $application,
        protected string $status
    ) {
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
        $statusKey = strtolower(trim($this->status));

        $subject = match ($statusKey) {
            'accepted' => __('notifications.announcement.application_status_subject.accepted'),
            'rejected' => __('notifications.announcement.application_status_subject.rejected'),
            'expired' => __('notifications.announcement.application_status_subject.expired'),
            'withdrawn' => __('notifications.announcement.application_status_subject.withdrawn'),
            default => __('notifications.announcement.application_status_subject.updated'),
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

    /**
     * @return array<string, mixed>
     */
    public function toExpoPush(object $notifiable): array
    {
        $application = $this->application->loadMissing([
            'announcement.parent.parentProfile',
            'babysitter.babysitterProfile',
        ]);

        $announcement = $application->announcement;
        $parentName = $this->resolveParentName($announcement?->parent);
        $statusKey = strtolower(trim($this->status));

        $subject = match ($statusKey) {
            'accepted' => __('notifications.announcement.application_status_subject.accepted'),
            'rejected' => __('notifications.announcement.application_status_subject.rejected'),
            'expired' => __('notifications.announcement.application_status_subject.expired'),
            'withdrawn' => __('notifications.announcement.application_status_subject.withdrawn'),
            default => __('notifications.announcement.application_status_subject.updated'),
        };

        $bodyKey = "emails.announcements.application_status.message.{$statusKey}";
        $body = __($bodyKey, ['parent' => $parentName]);
        if ($body === $bodyKey) {
            $body = __('emails.announcements.application_status.message.updated');
        }

        return [
            'title' => $subject,
            'body' => $body,
            'data' => [
                'type' => 'announcement',
                'id' => $announcement?->id,
                'application_id' => $application->id,
                'status' => $statusKey,
                'action' => 'application_status',
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
