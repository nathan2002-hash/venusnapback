@component('mail::message')
# 🧾 Payment Receipt (Copy)

Hello {{ $payment->user->name ?? 'there' }},
Here’s a **copy of your payment receipt** for your records.

@component('mail::panel')
**💵 Amount Paid:** ${{ number_format($payment->amount, 2) }}<br>
**🧾 Transaction ID:** {{ $payment->id }}<br>
**📅 Date:** {{ $payment->created_at->format('F j, Y, g:i a T') }}<br>
**📌 Status:** {{ ucfirst($payment->status) }}<br>
**💳 Payment Method:** Card<br>
**🎯 Purpose:** {{ $payment->purpose }}<br>
@if(isset($payment->metadata['points']))
**⭐ Points Added:** {{ $payment->metadata['points'] }}<br>
@endif
@endcomponent

---

### 📄 Description
{{ $payment->description }}

---

> 🔁 **This is a resend of your original receipt, sent at your request.**
> If you didn’t request this, please ignore or reply to this message for assistance.

Thank you for supporting creativity on **Venusnap** — your points are already active!
Let the Snaps begin 🚀

If you have any questions, just reply to this email.

**Warm regards,**
**– The Venusnap Billing Team**

@endcomponent
