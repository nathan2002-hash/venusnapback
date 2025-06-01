@component('mail::message')
# Your Points Request Has Been Received

Thank you for your points request. Here are the details:

@component('mail::panel')
**Request ID:** #{{ $request->id }}
**Name:** {{ $request->full_name }}
@if($request->business_name)
**Business:** {{ $request->business_name }}
@endif
**Points Requested:** {{ number_format($request->points) }}
**Purpose:**
{{ $request->purpose }}
@endcomponent

We will review your request and contact you shortly.
Expected response time: 1-2 business days.

If you have any questions, please reply to this email.

Thanks,
{{ config('app.name') }}
@endcomponent
