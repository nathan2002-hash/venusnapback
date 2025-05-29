<x-mail::message>
# Thank You for Contacting Venusnap

Hi {{ $formData['name'] }},

We’ve received your message regarding **{{ $formData['subject'] }}** and will get back to you as soon as possible.

Here’s a summary of your message:

@component('mail::panel')
{{ $formData['message'] }}
@endcomponent

If this was sent in error or you need urgent help, feel free to reply to this email.

Thanks for reaching out,
**The Venusnap Team**
</x-mail::message>
