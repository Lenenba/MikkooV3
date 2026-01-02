@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.invoices.issued.heading', ['number' => $invoice->number ?? $invoice->id]) }}

{{ __('emails.common.greeting', ['name' => $parentName ? ' ' . $parentName : '' ]) }}

{{ __('emails.invoices.issued.intro', [
    'start' => optional($invoice->period_start)->format('d/m/Y') ?? '-',
    'end' => optional($invoice->period_end)->format('d/m/Y') ?? '-',
]) }}

@component('mail::panel')
{{ __('common.roles.babysitter') }} : {{ $babysitterName }}
{{ __('common.labels.subtotal') }} : {{ number_format((float) ($invoice->subtotal_amount ?? 0), 2) }} {{ $invoice->currency }}
{{ __('emails.invoices.issued.tax_label', ['rate' => number_format((float) $vatPercent, 2)]) }} : {{ number_format((float) ($invoice->tax_amount ?? 0), 2) }} {{ $invoice->currency }}
{{ __('common.labels.total') }} : {{ number_format((float) ($invoice->total_amount ?? 0), 2) }} {{ $invoice->currency }}
@endcomponent

@component('mail::table')
| {{ __('emails.invoices.issued.table.service') }} | {{ __('emails.invoices.issued.table.date') }} | {{ __('emails.invoices.issued.table.total') }} |
|:--|:--|--:|
@foreach($invoice->items as $item)
| {{ $item->description }} | {{ optional($item->service_date)->format('d/m/Y') ?? '-' }} | {{ number_format((float) ($item->total_amount ?? 0), 2) }} {{ $invoice->currency }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('invoices.index')])
{{ __('emails.invoices.issued.button') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
