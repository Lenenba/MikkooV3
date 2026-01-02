<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var \App\Models\Reservation */
    protected Reservation $reservation;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Reservation  $reservation
     */
    public function __construct(Reservation $reservation)
    {
        // English comment: store the confirmed reservation
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation->loadMissing([
            'parent.parentProfile',
            'babysitter.babysitterProfile',
            'details',
            'services',
        ]);

        $status = $reservation->details?->status ?? 'updated';
        $subject = match ($status) {
            'confirmed' => 'Reservation confirmee',
            'completed' => 'Reservation terminee',
            'canceled' => 'Reservation annulee',
            default => 'Mise a jour de votre reservation',
        };

        $recipientName = $this->resolveRecipientName($notifiable);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.reservations.status', [
                'reservation' => $reservation,
                'details' => $reservation->details,
                'status' => $status,
                'recipientName' => $recipientName,
            ]);
    }

    protected function resolveRecipientName(object $notifiable): string
    {
        if (! method_exists($notifiable, 'parentProfile') || ! method_exists($notifiable, 'babysitterProfile')) {
            return '';
        }

        $profile = $notifiable->parentProfile ?? $notifiable->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) ($notifiable->name ?? ''));

        return $fallback !== '' ? $fallback : '';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
