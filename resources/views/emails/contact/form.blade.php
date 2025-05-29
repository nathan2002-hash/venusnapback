<x-mail::message>
# New Contact Form Submission

**Name:** {{ $formData['name'] }}
**Email:** {{ $formData['email'] }}
**Phone:** {{ $formData['phone'] }}
**Service:** {{ $formData['subject'] }}

## Message:
{{ $formData['message'] }}

@if(str_contains(strtolower($formData['message']), 'invest') || str_contains(strtolower($formData['subject']), 'invest'))
**Investment Request Detected** - Priority Attention Needed
@endif

{{-- @component('mail::button', ['url' => 'mailto:'.$formData['email'], 'color' => 'purple'])
Reply to {{ $formData['name'] }}
@endcomponent --}}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
