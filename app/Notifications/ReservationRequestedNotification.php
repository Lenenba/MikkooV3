<?php

namespace App\Notifications;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationRequestedNotification extends Notification
{
    use Queueable;

    protected Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation->loadMissing([
            'parent.parentProfile',
            'babysitter.babysitterProfile',
            'details',
            'services',
        ]);

        $details = $reservation->details;
        $parentName = $this->resolveParentName($reservation->parent);
        $babysitterName = $this->resolveBabysitterName($reservation->babysitter);
        $serviceNames = $reservation->services
            ->pluck('name')
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->values()
            ->all();

        return (new MailMessage)
            ->subject('Nouvelle reservation recue')
            ->markdown('emails.reservations.request', [
                'reservation' => $reservation,
                'details' => $details,
                'parentName' => $parentName,
                'babysitterName' => $babysitterName,
                'serviceNames' => $serviceNames,
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
