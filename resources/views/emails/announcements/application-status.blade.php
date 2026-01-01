@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

@php
    $statusKey = $status ?? 'updated';
    $labelMap = [
        'accepted' => 'Candidature acceptee',
        'rejected' => 'Candidature refusee',
        'expired' => 'Candidature expiree',
        'withdrawn' => 'Candidature retiree',
        'updated' => 'Mise a jour de candidature',
    ];
    $title = $labelMap[$statusKey] ?? $labelMap['updated'];
    $actionUrl = $statusKey === 'accepted' && $application?->reservation_id
        ? route('reservations.show', $application->reservation_id)
        : route('announcements.show', $announcement?->id);
    $actionLabel = $statusKey === 'accepted' ? 'Voir la reservation' : 'Voir l\'annonce';
@endphp

# {{ $title }}

Bonjour{{ $babysitterName ? ' ' . $babysitterName : '' }},

@if($statusKey === 'accepted')
Votre candidature a ete acceptee par {{ $parentName }}.
@elseif($statusKey === 'rejected')
Le parent a selectionne une autre babysitter.
@elseif($statusKey === 'expired')
Votre candidature a expire faute de reponse.
@elseif($statusKey === 'withdrawn')
Votre candidature a ete retiree.
@else
Votre candidature a ete mise a jour.
@endif

@component('mail::panel')
Annonce : {{ $announcement?->title ?? '-' }}
Service : {{ $announcement?->service ?? '-' }}
Date : {{ $announcement?->start_date ?? '-' }}
Heure : {{ $announcement?->start_time ?? '-' }} - {{ $announcement?->end_time ?? '-' }}
@endcomponent

@component('mail::button', ['url' => $actionUrl])
{{ $actionLabel }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
