<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\AnnouncementApplication;
use App\Models\Invoice;
use App\Notifications\AnnouncementApplicationStatusNotification;
use App\Notifications\InvoiceIssuedNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('announcements:expire-applications', function () {
    $expired = AnnouncementApplication::query()
        ->where('status', AnnouncementApplication::STATUS_PENDING)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->with(['babysitter', 'reservation.details', 'announcement.parent'])
        ->get();

    foreach ($expired as $application) {
        $application->update([
            'status' => AnnouncementApplication::STATUS_EXPIRED,
            'decided_at' => now(),
        ]);

        if ($application->reservation) {
            $application->reservation->details()->update([
                'status' => 'canceled',
            ]);
        }

        try {
            $application->babysitter?->notify(
                new AnnouncementApplicationStatusNotification($application, AnnouncementApplication::STATUS_EXPIRED)
            );
        } catch (\Throwable $exception) {
            Log::warning('Announcement application expiration notification failed.', [
                'application_id' => $application->id,
                'babysitter_id' => $application->babysitter_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    $this->info('Expired applications processed: ' . $expired->count());
})->purpose('Expire pending announcement applications');

Artisan::command('invoices:issue', function () {
    $dueDays = (int) config('billing.invoice_due_days', 14);
    $today = now()->startOfDay();

    $invoices = Invoice::query()
        ->where('status', 'draft')
        ->whereNotNull('period_end')
        ->whereDate('period_end', '<=', $today->toDateString())
        ->whereHas('items')
        ->with(['parent', 'items'])
        ->get();

    foreach ($invoices as $invoice) {
        $invoice->update([
            'status' => 'issued',
            'issued_at' => now(),
            'due_at' => $invoice->due_at ?? now()->addDays($dueDays),
        ]);

        try {
            $invoice->parent?->notify(new InvoiceIssuedNotification($invoice));
        } catch (\Throwable $exception) {
            Log::warning('Invoice issue notification failed.', [
                'invoice_id' => $invoice->id,
                'parent_id' => $invoice->parent_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    $this->info('Issued invoices: ' . $invoices->count());
})->purpose('Issue draft invoices whose period has ended');
