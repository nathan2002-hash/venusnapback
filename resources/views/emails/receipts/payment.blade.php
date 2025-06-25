@component('mail::message')
# Payment Receipt

@component('mail::panel')
## Payment Details
**Amount Paid:** ${{ number_format($payment->amount, 2) }}
**Transaction ID:** {{ $payment->id }}
**Date:** {{ $payment->created_at->format('F j, Y, g:i a T') }}
**Status:** {{ ucfirst($payment->status) }}
**Payment Method:** Card
**Purpose:** {{ $payment->purpose }}
@if(isset($payment->metadata['points']))
**Points Added:** {{ $payment->metadata['points'] }}
@endif
@endcomponent


## Description
{{ $payment->description }}

---

Thank you for powering your creativity your points are now live!
Let the Snaps begin

If you have any questions, simply reply to this email.

**Blessings,**
**The Venusnap Billing Team**
[support@venusnap.com](mailto:support@venusnap.com)
[venusnap.com](https://venusnap.com)

@endcomponent
