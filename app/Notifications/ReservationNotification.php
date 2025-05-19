<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationNotification extends Notification
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
        // English comment: use a markdown template with subject and data
        return (new MailMessage)
            ->subject('Votre réservation a été confirmée')
            ->markdown(
                'reservations.confirmed',
                [
                    'reservation' => $this->reservation,
                    'status' => $this->reservation->status,
                ]
            );
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
