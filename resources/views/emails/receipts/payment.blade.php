@component('mail::message')
# Payment Receipt

@component('mail::panel')
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

**Description**
{{ $payment->description }}

Thanks for powering your creativity, your points are now live. Let the Snaps begin
If you have questions, reply to this email.

Regards,
Billing Team
@endcomponent
