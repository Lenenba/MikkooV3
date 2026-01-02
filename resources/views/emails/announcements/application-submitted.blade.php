@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.announcements.application_submitted.heading') }}

{{ __('emails.common.greeting', ['name' => $babysitterName ? ' ' . $babysitterName : '' ]) }}

{{ __('emails.announcements.application_submitted.intro') }}

@php
    $serviceList = $announcement?->resolveServices() ?? [];
    $serviceLabel = !empty($serviceList) ? implode(', ', $serviceList) : ($announcement?->service ?? '-');
@endphp

@component('mail::panel')
{{ __('emails.announcements.labels.announcement') }} : {{ $announcement?->title ?? '-' }}
{{ __('emails.announcements.labels.service') }} : {{ $serviceLabel }}
{{ __('emails.announcements.labels.parent') }} : {{ $parentName }}
{{ __('emails.announcements.labels.date') }} : {{ $announcement?->start_date ?? '-' }}
{{ __('emails.announcements.labels.time') }} : {{ $announcement?->start_time ?? '-' }} - {{ $announcement?->end_time ?? '-' }}
@if($application?->message)
{{ __('emails.announcements.labels.your_message') }} : {{ $application->message }}
@endif
@endcomponent

@component('mail::button', ['url' => route('announcements.show', $announcement?->id)])
{{ __('emails.announcements.application_submitted.button') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
