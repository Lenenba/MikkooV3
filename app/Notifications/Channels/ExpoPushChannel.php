<?php

namespace App\Notifications\Channels;

use App\Models\PushToken;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushChannel
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toExpoPush')) {
            return;
        }

        $message = $notification->toExpoPush($notifiable);
        if (! is_array($message)) {
            return;
        }

        $tokens = method_exists($notifiable, 'routeNotificationForExpoPush')
            ? $notifiable->routeNotificationForExpoPush($notification)
            : [];

        if (! is_array($tokens) || $tokens === []) {
            return;
        }

        $tokens = array_values(array_filter($tokens, static function ($token) {
            return is_string($token) && trim($token) !== '';
        }));

        if ($tokens === []) {
            return;
        }

        $payloads = [];
        foreach ($tokens as $token) {
            $payload = [
                'to' => $token,
                'title' => $message['title'] ?? null,
                'body' => $message['body'] ?? null,
                'data' => $message['data'] ?? null,
                'sound' => $message['sound'] ?? 'default',
                'channelId' => $message['channelId'] ?? 'default',
            ];
            $payloads[] = array_filter(
                $payload,
                static fn($value) => $value !== null
            );
        }

        foreach (array_chunk($payloads, 100) as $chunk) {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->post(self::ENDPOINT, $chunk);

            if (! $response->ok()) {
                Log::warning('Expo push send failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                continue;
            }

            $data = $response->json('data') ?? [];
            if (! is_array($data)) {
                continue;
            }

            $invalidTokens = [];
            foreach ($data as $index => $ticket) {
                if (! is_array($ticket)) {
                    continue;
                }
                if (($ticket['status'] ?? null) !== 'error') {
                    continue;
                }
                $error = $ticket['details']['error'] ?? null;
                if (in_array($error, ['DeviceNotRegistered', 'InvalidCredentials'], true)) {
                    $invalidTokens[] = $chunk[$index]['to'] ?? null;
                } else {
                    Log::warning('Expo push ticket error.', [
                        'error' => $error,
                        'ticket' => $ticket,
                    ]);
                }
            }

            $invalidTokens = array_values(array_filter($invalidTokens));
            if ($invalidTokens !== []) {
                PushToken::query()->whereIn('token', $invalidTokens)->delete();
            }
        }
    }
}
