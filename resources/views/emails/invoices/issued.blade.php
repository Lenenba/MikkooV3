@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Facture {{ $invoice->number ?? $invoice->id }}

Bonjour{{ $parentName ? ' ' . $parentName : '' }},

Votre facture pour la periode du {{ optional($invoice->period_start)->format('d/m/Y') ?? '-' }}
au {{ optional($invoice->period_end)->format('d/m/Y') ?? '-' }} est disponible.

@component('mail::panel')
Babysitter : {{ $babysitterName }}
Sous-total : {{ number_format((float) ($invoice->subtotal_amount ?? 0), 2) }} {{ $invoice->currency }}
TVA ({{ number_format((float) $vatPercent, 2) }}%) : {{ number_format((float) ($invoice->tax_amount ?? 0), 2) }} {{ $invoice->currency }}
Total : {{ number_format((float) ($invoice->total_amount ?? 0), 2) }} {{ $invoice->currency }}
@endcomponent

@component('mail::table')
| Prestation | Date | Total |
|:--|:--|--:|
@foreach($invoice->items as $item)
| {{ $item->description }} | {{ optional($item->service_date)->format('d/m/Y') ?? '-' }} | {{ number_format((float) ($item->total_amount ?? 0), 2) }} {{ $invoice->currency }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('invoices.index')])
Voir mes factures
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
