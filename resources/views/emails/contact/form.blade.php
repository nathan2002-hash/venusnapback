<x-mail::message>
# New Contact Form Submission

**Name:** {{ $formData['name'] }}
**Email:** {{ $formData['email'] }}
**Phone:** {{ $formData['phone'] }}
**Service:** {{ $formData['subject'] }}

## Message:
@component('mail::panel')
{{ $formData['message'] }}
@endcomponent

@if(str_contains(strtolower($formData['message']), 'invest') || str_contains(strtolower($formData['subject']), 'invest'))
**Investment Request Detected** - Priority Attention Needed
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
