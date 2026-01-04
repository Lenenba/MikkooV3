<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\ExpoPushChannel;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['mail', ExpoPushChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->resolveName($this->user);

        return (new MailMessage)
            ->subject(__('notifications.welcome.subject'))
            ->markdown('emails.welcome', [
                'user' => $this->user,
                'name' => $name,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toExpoPush(object $notifiable): array
    {
        $name = $this->resolveName($this->user);
        $body = __('emails.welcome.intro', ['app' => config('app.name')]);

        return [
            'title' => __('notifications.welcome.subject'),
            'body' => $body,
            'data' => [
                'type' => 'welcome',
                'name' => $name,
            ],
        ];
    }

    protected function resolveName(User $user): ?string
    {
        $profile = $user->parentProfile ?? $user->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : null;
    }
}
