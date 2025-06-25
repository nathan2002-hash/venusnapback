@component('mail::message')
# Payment Receipt

Hello {{ $payment->user->name ?? 'Customer' }},

Thank you for your payment. Below is your receipt for your records.

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

@component('mail::subcopy')
This receipt was sent at your request.
If you didn't request this email, please contact our support team.
@endcomponent

Thank you for supporting creativity on **Venusnap** — your points are already active!
Let the Snaps begin

If you have any questions about this receipt, simply reply to this email.

**Venusnap Billing Team**
[support@venusnap.com](mailto:support@venusnap.com)
[venusnap.com](https://venusnap.com)

@endcomponent
