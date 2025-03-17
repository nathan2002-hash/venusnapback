<x-mail::message>
# Hello {{ $name }},

Your Two-Factor Authentication (2FA) code is:

# **{{ $code }}**

This code is valid for 10 minutes. If you did not request this, please secure your account.

Thanks,
**{{ config('app.name') }} Team**

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
