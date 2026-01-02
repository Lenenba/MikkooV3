<?php

namespace App\Notifications;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RatingReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Rating $rating;

    public function __construct(Rating $rating)
    {
        $this->rating = $rating;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rating = $this->rating->loadMissing([
            'reviewer',
            'reservation',
        ]);

        $reviewerName = $this->resolveUserName($rating->reviewer);
        $reservationNumber = $rating->reservation?->number ?? (string) $rating->reservation_id;
        $comment = trim((string) ($rating->comment ?? ''));

        return (new MailMessage)
            ->subject('Nouvelle evaluation recue')
            ->markdown('emails.ratings.received', [
                'rating' => $rating,
                'reviewerName' => $reviewerName,
                'reservationNumber' => $reservationNumber,
                'comment' => $comment,
            ]);
    }

    protected function resolveUserName(?User $user): string
    {
        if (! $user) {
            return 'Utilisateur';
        }

        $profile = $user->parentProfile ?? $user->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : 'Utilisateur';
    }
}
