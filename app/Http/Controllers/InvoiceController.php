<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Invoice::query()
            ->with([
                'parent.parentProfile',
                'babysitter.babysitterProfile',
            ])
            ->orderByDesc('created_at');

        if (! $user) {
            $query->whereRaw('0 = 1');
        } elseif ($user->isParent()) {
            $query
                ->where('parent_id', $user->id)
                ->whereIn('status', ['issued', 'paid']);
        } elseif ($user->isBabysitter()) {
            $query->where('babysitter_id', $user->id);
        }

        $stats = $this->buildStats($user);

        $invoices = $query
            ->paginate(15)
            ->through(function (Invoice $invoice): array {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'status' => $invoice->status,
                    'currency' => $invoice->currency,
                    'subtotal_amount' => $invoice->subtotal_amount,
                    'tax_amount' => $invoice->tax_amount,
                    'total_amount' => $invoice->total_amount,
                    'period_start' => $invoice->period_start?->toDateString(),
                    'period_end' => $invoice->period_end?->toDateString(),
                    'issued_at' => $invoice->issued_at?->toDateString(),
                    'due_at' => $invoice->due_at?->toDateString(),
                    'paid_at' => $invoice->paid_at?->toDateString(),
                    'parent' => $invoice->parent,
                    'babysitter' => $invoice->babysitter,
                ];
            });

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }

    public function show(Invoice $invoice)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            // Super admins can view all invoices.
        } elseif ($user->isParent()) {
            if ((int) $invoice->parent_id !== (int) $user->id) {
                abort(403);
            }
            if (! in_array($invoice->status, ['issued', 'paid'], true)) {
                abort(403);
            }
        } elseif ($user->isBabysitter()) {
            if ((int) $invoice->babysitter_id !== (int) $user->id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $invoice->loadMissing([
            'items',
            'parent.parentProfile',
            'babysitter.babysitterProfile',
        ]);

        return Inertia::render('invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function download(Invoice $invoice)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            // Super admins can download any invoice.
        } elseif ($user->isParent()) {
            if ((int) $invoice->parent_id !== (int) $user->id) {
                abort(403);
            }
            if (! in_array($invoice->status, ['issued', 'paid'], true)) {
                abort(403);
            }
        } elseif ($user->isBabysitter()) {
            if ((int) $invoice->babysitter_id !== (int) $user->id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $invoice->loadMissing([
            'items',
            'parent.parentProfile',
            'babysitter.babysitterProfile',
        ]);

        $parentName = $this->resolveName($invoice->parent, __('common.roles.parent'));
        $babysitterName = $this->resolveName($invoice->babysitter, __('common.roles.babysitter'));
        $vatPercent = round(((float) $invoice->vat_rate) * 100, 2);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'parentName' => $parentName,
            'babysitterName' => $babysitterName,
            'vatPercent' => $vatPercent,
        ])->setPaper('a4');

        $filename = sprintf('facture-%s.pdf', $invoice->number ?? $invoice->id);

        return $pdf->download($filename);
    }

    public function update(Request $request, Invoice $invoice, InvoiceService $invoiceService)
    {
        $user = Auth::user();
        if (! $user || ! $user->isBabysitter() || (int) $invoice->babysitter_id !== (int) $user->id) {
            abort(403);
        }

        if ($invoice->status !== 'draft') {
            return back()->withErrors(['invoice' => __('invoices.errors.only_draft')]);
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:invoice_items,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.service_date' => ['nullable', 'date'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice->loadMissing('items');
        $itemsById = $invoice->items->keyBy('id');

        foreach ($data['items'] as $itemData) {
            $itemId = (int) $itemData['id'];
            $item = $itemsById->get($itemId);
            if (! $item) {
                continue;
            }

            $quantity = round((float) $itemData['quantity'], 2);
            $unitPrice = round((float) $itemData['unit_price'], 2);
            $subtotal = round($quantity * $unitPrice, 2);
            $taxAmount = round($subtotal * (float) $invoice->vat_rate, 2);
            $totalAmount = round($subtotal + $taxAmount, 2);

            $item->update([
                'description' => $itemData['description'],
                'service_date' => $itemData['service_date'] ?? $item->service_date,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal_amount' => $subtotal,
                'vat_rate' => $invoice->vat_rate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);
        }

        $invoiceService->recalculate($invoice);

        return back()->with('success', __('flash.invoice.updated'));
    }

    protected function buildStats($user): array
    {
        if (! $user) {
            return [];
        }

        $baseQuery = Invoice::query();
        if ($user->isParent()) {
            $baseQuery->where('parent_id', $user->id);
        } elseif ($user->isBabysitter()) {
            $baseQuery->where('babysitter_id', $user->id);
        }

        $currency = (clone $baseQuery)->value('currency') ?? config('billing.default_currency', 'USD');

        if ($user->isAdmin()) {
            $draftCount = (clone $baseQuery)->where('status', 'draft')->count();
            $issuedCount = (clone $baseQuery)->where('status', 'issued')->count();
            $paidCount = (clone $baseQuery)->where('status', 'paid')->count();
            $issuedTotal = (float) (clone $baseQuery)->where('status', 'issued')->sum('total_amount');
            $paidTotal = (float) (clone $baseQuery)->where('status', 'paid')->sum('total_amount');

            return [
                'currency' => $currency,
                'draft_count' => $draftCount,
                'issued_count' => $issuedCount,
                'paid_count' => $paidCount,
                'issued_total' => $issuedTotal,
                'paid_total' => $paidTotal,
            ];
        }

        if ($user->isParent()) {
            $dueCount = (clone $baseQuery)->where('status', 'issued')->count();
            $paidCount = (clone $baseQuery)->where('status', 'paid')->count();
            $dueTotal = (float) (clone $baseQuery)->where('status', 'issued')->sum('total_amount');
            $paidTotal = (float) (clone $baseQuery)->where('status', 'paid')->sum('total_amount');

            return [
                'currency' => $currency,
                'due_count' => $dueCount,
                'paid_count' => $paidCount,
                'due_total' => $dueTotal,
                'paid_total' => $paidTotal,
            ];
        }

        $draftCount = (clone $baseQuery)->where('status', 'draft')->count();
        $issuedCount = (clone $baseQuery)->where('status', 'issued')->count();
        $paidCount = (clone $baseQuery)->where('status', 'paid')->count();
        $issuedTotal = (float) (clone $baseQuery)->where('status', 'issued')->sum('total_amount');
        $paidTotal = (float) (clone $baseQuery)->where('status', 'paid')->sum('total_amount');

        return [
            'currency' => $currency,
            'draft_count' => $draftCount,
            'issued_count' => $issuedCount,
            'paid_count' => $paidCount,
            'issued_total' => $issuedTotal,
            'paid_total' => $paidTotal,
        ];
    }

    private function resolveName($user, ?string $fallback = null): string
    {
        $fallbackName = $fallback ?? __('common.roles.parent');
        if (! $user) {
            return $fallbackName;
        }

        $profile = $user->parentProfile ?? $user->babysitterProfile;
        $first = trim((string) ($profile?->first_name ?? ''));
        $last = trim((string) ($profile?->last_name ?? ''));
        $full = trim($first . ' ' . $last);

        if ($full !== '') {
            return $full;
        }

        $name = trim((string) ($user->name ?? ''));

        return $name !== '' ? $name : $fallbackName;
    }
}
