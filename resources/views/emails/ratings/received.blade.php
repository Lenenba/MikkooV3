@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Nouvelle evaluation recue

Bonjour,

{{ $reviewerName }} a laisse une evaluation pour la reservation {{ $reservationNumber }}.

@component('mail::panel')
Note : {{ $rating->rating }}/5
@if($comment)
Commentaire : {{ $comment }}
@endif
@endcomponent

@component('mail::button', ['url' => route('reservations.show', $rating->reservation_id)])
Voir la reservation
@endcomponent

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
