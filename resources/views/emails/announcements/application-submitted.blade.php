@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Candidature envoyee

Bonjour{{ $babysitterName ? ' ' . $babysitterName : '' }},

Votre proposition a bien ete envoyee. Le parent recevra votre candidature et reviendra vers vous.

@php
    $serviceList = $announcement?->resolveServices() ?? [];
    $serviceLabel = !empty($serviceList) ? implode(', ', $serviceList) : ($announcement?->service ?? '-');
@endphp

@component('mail::panel')
Annonce : {{ $announcement?->title ?? '-' }}
Service : {{ $serviceLabel }}
Parent : {{ $parentName }}
Date : {{ $announcement?->start_date ?? '-' }}
Heure : {{ $announcement?->start_time ?? '-' }} - {{ $announcement?->end_time ?? '-' }}
@if($application?->message)
Votre message : {{ $application->message }}
@endif
@endcomponent

@component('mail::button', ['url' => route('announcements.show', $announcement?->id)])
Voir l'annonce
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
