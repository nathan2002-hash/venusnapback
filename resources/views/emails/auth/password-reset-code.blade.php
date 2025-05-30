@component('mail::message')
# Password Reset Request

Hello,

You requested to reset your password.
Use the code below to proceed:

@component('mail::panel')
## {{ $code }}
@endcomponent

This code will expire in **10 minutes**.

If you didn’t request a password reset, you can safely ignore this email.

Thanks,
**The Venusnap Team**
@endcomponent
