<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
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
            $query->where('parent_id', $user->id);
        } elseif ($user->isBabysitter()) {
            $query->where('babysitter_id', $user->id);
        }

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
        ]);
    }
}
