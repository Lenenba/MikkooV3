@component('mail::message')
{{-- Header slot (uses app name and link) --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Greeting --}}
# Bonjour {{ $reservation->parent->parentProfile->first_name }} {{ $reservation->parent->parentProfile->last_name }},

{{-- Main notification --}}
Nous vous informons que votre réservation du
**{{ $reservation->details->date }}**
de **{{ $reservation->details->start_time }}** à **{{ $reservation->details->end_time ??
'—' }}**
avec **{{ $reservation->babysitter->babysitterProfile->first_name }} {{ $reservation->babysitter->babysitterProfile->last_name }}**
a été **{{ ucfirst('Confirmed') }}**.

{{-- Optional panel with key details --}}
@component('mail::panel')
**Détails de la réservation :**
- **Date :** {{ $reservation->details->date }}
- **Heure :** {{ $reservation->details->start_time }} à {{ $reservation->details->end_time
?? '—' }}
- **Babysitter :** {{ $reservation->babysitter->babysitterProfile->first_name }} {{ $reservation->babysitter->babysitterProfile->last_name
}}
@endcomponent

{{-- Call to action button --}}
@component('mail::button', ['url' => route('reservations.index')])
Voir mes réservations
@endcomponent

{{-- Conditional extra message --}}
@if($status === 'confirmed')
Merci de votre confiance, nous avons hâte de vous servir !
@elseif($status === 'canceled')
Nous sommes désolés pour ce contretemps. N’hésitez pas à reprogrammer votre réservation à votre convenance.
@endif

{{-- Subcopy --}}
@slot('subcopy')
Si vous rencontrez un problème ou avez des questions, contactez-nous à <a
    href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
@endslot

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
@endcomponent
@endslot
@endcomponent
