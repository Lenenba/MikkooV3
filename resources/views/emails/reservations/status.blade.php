@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

@php
    $statusLabel = $status === 'confirmed'
        ? 'confirmee'
        : ($status === 'completed' ? 'terminee' : ($status === 'canceled' ? 'annulee' : 'mise a jour'));
@endphp

# Reservation {{ $statusLabel }}

Bonjour{{ $recipientName ? ' ' . $recipientName : '' }},

Votre reservation {{ $reservation->number ?? $reservation->id }} a ete {{ $statusLabel }}.

@component('mail::panel')
Date : {{ $details?->date ?? '-' }}
Heure : {{ $details?->start_time ?? '-' }} - {{ $details?->end_time ?? '-' }}
Babysitter : {{ $reservation->babysitter?->babysitterProfile?->first_name ?? '' }} {{ $reservation->babysitter?->babysitterProfile?->last_name ?? '' }}
Parent : {{ $reservation->parent?->parentProfile?->first_name ?? '' }} {{ $reservation->parent?->parentProfile?->last_name ?? '' }}
Total : {{ number_format((float) ($reservation->total_amount ?? 0), 2) }}
@endcomponent

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
Voir la reservation
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
