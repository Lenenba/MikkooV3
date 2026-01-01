@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Nouvelle candidature recue

Bonjour{{ $parentName ? ' ' . $parentName : '' }},

{{ $babysitterName }} s'est propose pour votre annonce.

@component('mail::panel')
Titre : {{ $announcement?->title ?? '-' }}
Service : {{ $announcement?->service ?? '-' }}
Date : {{ $announcement?->start_date ?? '-' }}
Heure : {{ $announcement?->start_time ?? '-' }} - {{ $announcement?->end_time ?? '-' }}
@if($application?->message)
Message : {{ $application->message }}
@endif
@endcomponent

@component('mail::button', ['url' => route('announcements.show', $announcement?->id)])
Voir les candidatures
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
