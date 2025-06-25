@component('mail::message')
# ✅ Payment Receipt

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

Thank you for powering your creativity — your points are now live!
Let the Snaps begin 🚀

If you have any questions, simply reply to this email.

**Blessings,**
**– The Venusnap Billing Team**

@endcomponent
