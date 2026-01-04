<?php

namespace App\Notifications;

use App\Models\ReservationMediaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\ExpoPushChannel;

class ReservationMediaRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ReservationMediaRequest $mediaRequest,
        protected string $status = 'requested'
    ) {
    }

    public function via(object $notifiable): array
    {
        return [ExpoPushChannel::class];
    }

    /**
     * @return array<string, mixed>
     */
    public function toExpoPush(object $notifiable): array
    {
        $request = $this->mediaRequest->loadMissing([
            'reservation.details',
            'requester',
            'fulfiller',
        ]);

        $reservation = $request->reservation;
        $reference = $reservation?->number ?? (string) ($reservation?->id ?? '');

        $statusKey = strtolower(trim($this->status));
        $isFulfilled = $statusKey === 'fulfilled';

        $title = $isFulfilled
            ? __('notifications.reservation.media_request_fulfilled_subject')
            : __('notifications.reservation.media_request_subject');

        $bodyKey = $isFulfilled
            ? 'notifications.reservation.media_request_fulfilled_body'
            : 'notifications.reservation.media_request_body';

        $body = __($bodyKey, [
            'reference' => $reference,
        ]);

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'type' => 'reservation',
                'id' => $reservation?->id,
                'request_id' => $request->id,
                'action' => 'media_request',
                'status' => $isFulfilled ? 'fulfilled' : 'requested',
            ],
        ];
    }
}
