<x-mail::message>
# Hello {{ $user->name }},

Your verification code is:

@component('mail::panel')
## {{ $code }}
@endcomponent

This code will expire in 15 minutes.

If you didn't request this code, please ignore this email.

Thanks, Security Team<br>
{{ config('app.name') }}
</x-mail::message>
