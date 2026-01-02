@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.reservations.confirmed.heading') }}

@php
    $parentFirst = trim((string) ($reservation->parent->parentProfile->first_name ?? ''));
    $greetingName = $parentFirst !== '' ? ' ' . $parentFirst : '';
@endphp
{{ __('emails.common.greeting', ['name' => $greetingName]) }}

{{ __('emails.reservations.confirmed.intro', [
    'date' => $reservation->details->date ?? '-',
    'start' => $reservation->details->start_time ?? '-',
    'end' => $reservation->details->end_time ?? '-',
    'babysitter' => trim(($reservation->babysitter->babysitterProfile->first_name ?? '') . ' ' . ($reservation->babysitter->babysitterProfile->last_name ?? '')),
]) }}

@component('mail::panel')
**{{ __('emails.reservations.confirmed.details') }} :**
- **{{ __('common.labels.date') }} :** {{ $reservation->details->date ?? '-' }}
- **{{ __('common.labels.time') }} :** {{ $reservation->details->start_time ?? '-' }} - {{ $reservation->details->end_time ?? '-' }}
- **{{ __('common.roles.babysitter') }} :** {{ $reservation->babysitter->babysitterProfile->first_name ?? '' }} {{ $reservation->babysitter->babysitterProfile->last_name ?? '' }}
@endcomponent

@component('mail::button', ['url' => route('reservations.index')])
{{ __('emails.reservations.confirmed.button') }}
@endcomponent

@if($status === 'confirmed')
{{ __('emails.reservations.confirmed.message.confirmed') }}
@elseif($status === 'canceled')
{{ __('emails.reservations.confirmed.message.canceled') }}
@endif

@slot('subcopy')
{{ __('emails.reservations.confirmed.subcopy', ['email' => config('mail.from.address')]) }}
@endslot

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
