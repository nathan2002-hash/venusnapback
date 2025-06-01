@component('mail::message')
# New Points Request

A user has submitted a new points request:

@component('mail::panel')
**Request ID:** #{{ $request->id }}
**Name:** {{ $request->full_name }}
@if($request->business_name)
**Business:** {{ $request->business_name }}
@endif
**Email:** {{ $request->email }}
**Phone:** {{ $request->phone }}
**Points Requested:** {{ number_format($request->points) }}
**Purpose:**
{{ $request->purpose }}
@endcomponent

@component('mail::button', ['url' => url('/restricted/points/requests/'.$request->id)])
View Request in Admin Panel
@endcomponent

**User Account:**
@if($request->user)
- **Name:** {{ $request->user->name }}
- **Email:** {{ $request->user->email }}
- **Current Points:** {{ number_format($request->user->points) }}
@else
Guest user (not registered)
@endif

Thanks,
{{ config('app.name') }}
@endcomponent
