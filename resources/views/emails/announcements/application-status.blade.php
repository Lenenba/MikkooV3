@component('mail::message')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

@php
    $statusKey = $status ?? 'updated';
    $labelMap = __('emails.announcements.application_status.status_labels');
    $title = is_array($labelMap)
        ? ($labelMap[$statusKey] ?? ($labelMap['updated'] ?? $statusKey))
        : $statusKey;
    $actionUrl = $statusKey === 'accepted' && $application?->reservation_id
        ? route('reservations.show', $application->reservation_id)
        : route('announcements.show', $announcement?->id);
    $actionLabel = $statusKey === 'accepted'
        ? __('emails.announcements.application_status.action.reservation')
        : __('emails.announcements.application_status.action.announcement');
    $serviceList = $announcement?->resolveServices() ?? [];
    $serviceLabel = !empty($serviceList) ? implode(', ', $serviceList) : ($announcement?->service ?? '-');
@endphp

# {{ $title }}

{{ __('emails.common.greeting', ['name' => $babysitterName ? ' ' . $babysitterName : '' ]) }}

@if($statusKey === 'accepted')
{{ __('emails.announcements.application_status.message.accepted', ['parent' => $parentName]) }}
@elseif($statusKey === 'rejected')
{{ __('emails.announcements.application_status.message.rejected') }}
@elseif($statusKey === 'expired')
{{ __('emails.announcements.application_status.message.expired') }}
@elseif($statusKey === 'withdrawn')
{{ __('emails.announcements.application_status.message.withdrawn') }}
@else
{{ __('emails.announcements.application_status.message.updated') }}
@endif

@component('mail::panel')
{{ __('emails.announcements.labels.announcement') }} : {{ $announcement?->title ?? '-' }}
{{ __('emails.announcements.labels.service') }} : {{ $serviceLabel }}
{{ __('emails.announcements.labels.date') }} : {{ $announcement?->start_date ?? '-' }}
{{ __('emails.announcements.labels.time') }} : {{ $announcement?->start_time ?? '-' }} - {{ $announcement?->end_time ?? '-' }}
@endcomponent

@component('mail::button', ['url' => $actionUrl])
{{ $actionLabel }}
@endcomponent

@slot('footer')
@component('mail::footer')
{{ __('emails.common.footer', ['year' => date('Y'), 'app' => config('app.name')]) }}
@endcomponent
@endslot
@endcomponent
