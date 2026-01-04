<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\ExpoPushChannel;

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
        return ['mail', ExpoPushChannel::class];
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
            'confirmed' => __('notifications.reservation.status_subject.confirmed'),
            'in_progress' => __('notifications.reservation.status_subject.in_progress'),
            'completed' => __('notifications.reservation.status_subject.completed'),
            'canceled' => __('notifications.reservation.status_subject.canceled'),
            default => __('notifications.reservation.status_subject.updated'),
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

    /**
     * @return array<string, mixed>
     */
    public function toExpoPush(object $notifiable): array
    {
        $reservation = $this->reservation->loadMissing([
            'parent.parentProfile',
            'babysitter.babysitterProfile',
            'details',
            'services',
        ]);

        $status = $reservation->details?->status ?? 'updated';
        $subject = match ($status) {
            'confirmed' => __('notifications.reservation.status_subject.confirmed'),
            'in_progress' => __('notifications.reservation.status_subject.in_progress'),
            'completed' => __('notifications.reservation.status_subject.completed'),
            'canceled' => __('notifications.reservation.status_subject.canceled'),
            default => __('notifications.reservation.status_subject.updated'),
        };

        $statusLabelKey = "emails.reservations.status.status_labels.{$status}";
        $statusLabel = __($statusLabelKey);
        if ($statusLabel === $statusLabelKey) {
            $statusLabel = $status;
        }

        $reference = $reservation->number ?? (string) $reservation->id;
        $body = __('emails.reservations.status.intro', [
            'reference' => $reference,
            'status' => $statusLabel,
        ]);

        return [
            'title' => $subject,
            'body' => $body,
            'data' => [
                'type' => 'reservation',
                'id' => $reservation->id,
                'status' => $status,
            ],
        ];
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
