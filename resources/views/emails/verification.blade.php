<x-mail::message>
# Hello {{ $user->name }},

Confirm your glow in the Venus galaxy. ✨

@component('mail::panel')
## {{ $code }}
@endcomponent

This code will expire in 15 minutes.

If you didn't request this code, please ignore this email and continue snapping.

Shining from the Verification Centre,
{{ config('app.name') }}
</x-mail::message>
