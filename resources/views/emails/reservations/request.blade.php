@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Nouvelle reservation recue

Bonjour{{ $babysitterName ? ' ' . $babysitterName : '' }},

Une nouvelle reservation a ete demandee par {{ $parentName }}.

@component('mail::panel')
Reference : {{ $reservation->number ?? $reservation->id }}
Date : {{ $details?->date ?? '-' }}
Heure : {{ $details?->start_time ?? '-' }} - {{ $details?->end_time ?? '-' }}
@if(!empty($serviceNames))
Services : {{ implode(', ', $serviceNames) }}
@endif
Total : {{ number_format((float) ($reservation->total_amount ?? 0), 2) }}
@endcomponent

@if($reservation->notes)
Notes : {{ $reservation->notes }}
@endif

@component('mail::button', ['url' => route('reservations.show', $reservation->id)])
Voir la reservation
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
