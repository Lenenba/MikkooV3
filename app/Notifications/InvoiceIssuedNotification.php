<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\ExpoPushChannel;

class InvoiceIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via(object $notifiable): array
    {
        return ['mail', ExpoPushChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice->loadMissing([
            'parent.parentProfile',
            'babysitter.babysitterProfile',
            'items',
        ]);

        $parentName = $this->resolveName($invoice->parent, __('common.roles.parent'));
        $babysitterName = $this->resolveName($invoice->babysitter, __('common.roles.babysitter'));
        $vatPercent = round(((float) $invoice->vat_rate) * 100, 2);

        return (new MailMessage)
            ->subject(__('notifications.invoice.issued_subject', [
                'number' => $invoice->number ?? $invoice->id,
            ]))
            ->markdown('emails.invoices.issued', [
                'invoice' => $invoice,
                'parentName' => $parentName,
                'babysitterName' => $babysitterName,
                'vatPercent' => $vatPercent,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toExpoPush(object $notifiable): array
    {
        $invoice = $this->invoice->loadMissing([
            'parent.parentProfile',
            'babysitter.babysitterProfile',
            'items',
        ]);

        $subject = __('notifications.invoice.issued_subject', [
            'number' => $invoice->number ?? $invoice->id,
        ]);

        $start = $invoice->period_start?->toDateString() ?? $invoice->issued_at?->toDateString();
        $end = $invoice->period_end?->toDateString() ?? $invoice->issued_at?->toDateString();
        $body = __('emails.invoices.issued.intro', [
            'start' => $start ?? '',
            'end' => $end ?? '',
        ]);

        return [
            'title' => $subject,
            'body' => $body,
            'data' => [
                'type' => 'invoice',
                'id' => $invoice->id,
            ],
        ];
    }

    private function resolveName(?User $user, string $fallback = ''): string
    {
        if (! $user) {
            return $fallback !== '' ? $fallback : __('common.roles.parent');
        }

        $profile = $user->parentProfile ?? $user->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $name = trim((string) $user->name);

        if ($name !== '') {
            return $name;
        }

        return $fallback !== '' ? $fallback : __('common.roles.parent');
    }
}
