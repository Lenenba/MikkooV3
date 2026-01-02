@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# {{ __('emails.announcements.match.heading') }}

{{ __('emails.common.greeting', ['name' => $babysitterName ? ' ' . $babysitterName : '' ]) }}

{{ __('emails.announcements.match.intro') }}

@php
    $serviceList = $announcement?->resolveServices() ?? [];
    $serviceLabel = !empty($serviceList) ? implode(', ', $serviceList) : ($announcement->service ?? '-');
@endphp

@component('mail::panel')
{{ __('emails.announcements.labels.title') }} : {{ $announcement->title }}
{{ __('emails.announcements.labels.service') }} : {{ $serviceLabel }}
{{ __('emails.announcements.labels.parent') }} : {{ $parentName }}
@if($city)
{{ __('emails.announcements.labels.city') }} : {{ $city }}
@endif
@if($childLabel)
{{ __('emails.announcements.labels.child') }} : {{ $childLabel }}
@endif
@if($announcement->child_notes)
{{ __('emails.announcements.labels.notes') }} : {{ $announcement->child_notes }}
@endif
@endcomponent

@if($announcement->description)
{{ __('emails.announcements.labels.details') }} : {{ $announcement->description }}
@endif

@component('mail::button', ['url' => route('announcements.show', $announcement->id)])
{{ __('emails.announcements.match.button') }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
