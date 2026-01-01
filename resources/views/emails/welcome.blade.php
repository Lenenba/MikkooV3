@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Bonjour{{ $name ? ' ' . $name : '' }}

Merci pour votre inscription sur {{ config('app.name') }}.

@component('mail::panel')
- Completez votre adresse
- Ajoutez vos preferences
- Definissez vos disponibilites
@endcomponent

@component('mail::button', ['url' => route('onboarding.index', ['step' => 2])])
Completer mon inscription
@endcomponent

Si vous avez besoin d'aide, repondez a ce message ou ecrivez a
{{ config('mail.from.address') }}.

@slot('footer')
@component('mail::footer')
{{ date('Y') }} {{ config('app.name') }}. Tous droits reserves.
@endcomponent
@endslot
@endcomponent
