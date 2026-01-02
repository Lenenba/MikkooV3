<?php

namespace App\Notifications;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AnnouncementMatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Announcement $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $announcement = $this->announcement->loadMissing([
            'parent.parentProfile',
            'parent.address',
        ]);

        $parentName = $this->resolveParentName($announcement->parent);
        $babysitterName = $this->resolveBabysitterName($notifiable instanceof User ? $notifiable : null);
        $childLabel = $this->buildChildLabel($announcement);
        $city = trim((string) ($announcement->parent?->address?->city ?? ''));

        return (new MailMessage)
            ->subject('Nouvelle annonce disponible')
            ->markdown('emails.announcements.match', [
                'announcement' => $announcement,
                'parentName' => $parentName,
                'babysitterName' => $babysitterName,
                'childLabel' => $childLabel,
                'city' => $city,
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

    protected function buildChildLabel(Announcement $announcement): ?string
    {
        $name = trim((string) ($announcement->child_name ?? ''));
        $age = $announcement->child_age !== null ? trim((string) $announcement->child_age) : '';

        $parts = array_filter([$name, $age !== '' ? $age . ' ans' : null]);
        $label = trim(implode(' - ', $parts));

        return $label !== '' ? $label : null;
    }
}
