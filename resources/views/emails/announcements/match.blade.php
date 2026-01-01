@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Nouvelle annonce disponible

Bonjour{{ $babysitterName ? ' ' . $babysitterName : '' }},

Une nouvelle annonce correspond a vos services.

@component('mail::panel')
Titre : {{ $announcement->title }}
Service : {{ $announcement->service }}
Parent : {{ $parentName }}
@if($city)
Ville : {{ $city }}
@endif
@if($childLabel)
Enfant : {{ $childLabel }}
@endif
@if($announcement->child_notes)
Notes : {{ $announcement->child_notes }}
@endif
@endcomponent

@if($announcement->description)
Details : {{ $announcement->description }}
@endif

@component('mail::button', ['url' => route('announcements.show', $announcement->id)])
Voir l'annonce
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
