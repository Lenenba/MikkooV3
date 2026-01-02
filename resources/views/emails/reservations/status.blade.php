@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

@php
    $statusKey = $status ?? 'updated';
    $statusLabels = __('emails.reservations.status.status_labels');
    $statusLabel = is_array($statusLabels)
        ? ($statusLabels[$statusKey] ?? ($statusLabels['updated'] ?? $statusKey))
        : $statusKey;
@endphp

# {{ __('emails.reservations.status.heading', ['status' => $statusLabel]) }}

{{ __('emails.common.greeting', ['name' => $recipientName ? ' ' . $recipientName : '' ]) }}

{{ __('emails.reservations.status.intro', ['reference' => $reservation->number ?? $reservation->id, 'status' => $statusLabel]) }}

@component('mail::panel')
{{ __('common.labels.date') }} : {{ $details?->date ?? '-' }}
{{ __('common.labels.time') }} : {{ $details?->start_time ?? '-' }} - {{ $details?->end_time ?? '-' }}
{{ __('common.roles.babysitter') }} : {{ $reservation->babysitter?->babysitterProfile?->first_name ?? '' }} {{ $reservation->babysitter?->babysitterProfile?->last_name ?? '' }}
{{ __('common.roles.parent') }} : {{ $reservation->parent?->parentProfile?->first_name ?? '' }} {{ $reservation->parent?->parentProfile?->last_name ?? '' }}
{{ __('common.labels.total') }} : {{ number_format((float) ($reservation->total_amount ?? 0), 2) }}
@endcomponent

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
{{ __('emails.reservations.status.button') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
