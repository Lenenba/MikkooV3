@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.common.greeting', ['name' => $name ? ' ' . $name : '' ]) }}

{{ __('emails.welcome.intro', ['app' => config('app.name')]) }}

@component('mail::panel')
- {{ __('emails.welcome.steps.address') }}
- {{ __('emails.welcome.steps.preferences') }}
- {{ __('emails.welcome.steps.availability') }}
@endcomponent

@component('mail::button', ['url' => route('onboarding.index', ['step' => 2])])
{{ __('emails.welcome.cta') }}
@endcomponent

{{ __('emails.welcome.help', ['email' => config('mail.from.address')]) }}

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
