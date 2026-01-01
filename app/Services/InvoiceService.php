<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Reservation;
use App\Support\Billing;
use Carbon\Carbon;

class InvoiceService
{
    public function createFromReservation(Reservation $reservation): ?Invoice
    {
        $reservation->loadMissing([
            'details',
            'reservationServices',
            'babysitter.babysitterProfile',
            'babysitter.address',
            'parent',
        ]);

        if ($reservation->details?->status !== 'completed') {
            return null;
        }

        if (InvoiceItem::query()->where('reservation_id', $reservation->id)->exists()) {
            return null;
        }

        $babysitter = $reservation->babysitter;
        $parent = $reservation->parent;

        if (! $babysitter || ! $parent) {
            return null;
        }

        $country = $babysitter->address?->country;
        $vatRate = Billing::vatRateForCountry($country);
        $currency = Billing::currencyForCountry($country);
        $frequency = $babysitter->babysitterProfile?->payment_frequency ?? 'per_task';

        $serviceDate = $reservation->details?->date
            ? Carbon::parse($reservation->details->date)
            : Carbon::now();

        [$periodStart, $periodEnd] = $this->resolvePeriod($serviceDate, $frequency);

        $invoice = $this->resolveInvoice(
            $reservation,
            $frequency,
            $periodStart,
            $periodEnd,
            $currency,
            $vatRate
        );

        $subtotal = $this->resolveReservationSubtotal($reservation, $vatRate);
        $taxAmount = round($subtotal * $vatRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        $invoice->items()->create([
            'reservation_id' => $reservation->id,
            'description' => $this->buildDescription($reservation, $serviceDate),
            'service_date' => $serviceDate->toDateString(),
            'quantity' => 1,
            'unit_price' => $subtotal,
            'subtotal_amount' => $subtotal,
            'vat_rate' => $vatRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        $this->recalculateInvoiceTotals($invoice);

        return $invoice;
    }

    public function recalculate(Invoice $invoice): void
    {
        $this->recalculateInvoiceTotals($invoice);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(Carbon $serviceDate, string $frequency): array
    {
        $frequency = strtolower($frequency);
        $date = $serviceDate->copy()->startOfDay();

        if ($frequency === 'monthly') {
            return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
        }

        if ($frequency === 'weekly') {
            return [$date->copy()->startOfWeek(Carbon::MONDAY), $date->copy()->endOfWeek(Carbon::SUNDAY)];
        }

        if ($frequency === 'biweekly') {
            $startOfWeek = $date->copy()->startOfWeek(Carbon::MONDAY);
            $weekNumber = (int) $startOfWeek->isoWeek();
            $periodStart = $weekNumber % 2 === 0 ? $startOfWeek->copy()->subWeek() : $startOfWeek;
            $periodEnd = $periodStart->copy()->addDays(13)->endOfDay();

            return [$periodStart->startOfDay(), $periodEnd];
        }

        return [$date, $date];
    }

    private function resolveInvoice(
        Reservation $reservation,
        string $frequency,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $currency,
        float $vatRate
    ): Invoice {
        if (strtolower($frequency) === 'per_task') {
            return Invoice::create([
                'babysitter_id' => $reservation->babysitter_id,
                'parent_id' => $reservation->parent_id,
                'status' => 'draft',
                'currency' => $currency,
                'vat_rate' => $vatRate,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ]);
        }

        $existing = Invoice::query()
            ->where('babysitter_id', $reservation->babysitter_id)
            ->where('parent_id', $reservation->parent_id)
            ->where('status', 'draft')
            ->whereDate('period_start', $periodStart->toDateString())
            ->whereDate('period_end', $periodEnd->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        return Invoice::create([
            'babysitter_id' => $reservation->babysitter_id,
            'parent_id' => $reservation->parent_id,
            'status' => 'draft',
            'currency' => $currency,
            'vat_rate' => $vatRate,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
        ]);
    }

    private function resolveReservationSubtotal(Reservation $reservation, float $vatRate): float
    {
        $subtotal = (float) $reservation->reservationServices->sum('total');

        if ($subtotal > 0) {
            return round($subtotal, 2);
        }

        $gross = (float) ($reservation->total_amount ?? 0);
        if ($gross <= 0) {
            return 0;
        }

        if ($vatRate > 0) {
            return round($gross / (1 + $vatRate), 2);
        }

        return round($gross, 2);
    }

    private function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $invoice->loadMissing('items');

        $subtotal = (float) $invoice->items->sum('subtotal_amount');
        $tax = (float) $invoice->items->sum('tax_amount');
        $total = (float) $invoice->items->sum('total_amount');

        $invoice->update([
            'subtotal_amount' => round($subtotal, 2),
            'tax_amount' => round($tax, 2),
            'total_amount' => round($total, 2),
        ]);
    }

    private function buildDescription(Reservation $reservation, Carbon $serviceDate): string
    {
        $ref = $reservation->number ?? $reservation->id;
        $dateLabel = $serviceDate->format('d/m/Y');

        return "Reservation {$ref} - {$dateLabel}";
    }
}
