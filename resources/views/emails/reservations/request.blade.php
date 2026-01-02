@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.reservations.request.heading') }}

{{ __('emails.common.greeting', ['name' => $babysitterName ? ' ' . $babysitterName : '' ]) }}

{{ __('emails.reservations.request.intro', ['parent' => $parentName]) }}

@component('mail::panel')
{{ __('common.labels.reference') }} : {{ $reservation->number ?? $reservation->id }}
{{ __('common.labels.date') }} : {{ $details?->date ?? '-' }}
{{ __('common.labels.time') }} : {{ $details?->start_time ?? '-' }} - {{ $details?->end_time ?? '-' }}
@if(!empty($serviceNames))
{{ __('common.labels.services') }} : {{ implode(', ', $serviceNames) }}
@endif
{{ __('common.labels.total') }} : {{ number_format((float) ($reservation->total_amount ?? 0), 2) }}
@endcomponent

@if($reservation->notes)
{{ __('common.labels.notes') }} : {{ $reservation->notes }}
@endif

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
{{ __('emails.reservations.request.button') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
