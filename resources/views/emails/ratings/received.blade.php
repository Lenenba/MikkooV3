@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.ratings.received.heading') }}

{{ __('emails.common.greeting', ['name' => '']) }}

{{ __('emails.ratings.received.intro', ['reviewer' => $reviewerName, 'reference' => $reservationNumber]) }}

@component('mail::panel')
{{ __('emails.ratings.received.rating_label') }} : {{ $rating->rating }}/5
@if($comment)
{{ __('emails.ratings.received.comment_label') }} : {{ $comment }}
@endif
@endcomponent

@component('mail::button', ['url' => route('reservations.show', $rating->reservation_id)])
{{ __('emails.ratings.received.button') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
