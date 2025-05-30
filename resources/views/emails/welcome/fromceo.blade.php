@component('mail::message')
# Welcome to Venusnap, {{ $user->name }} 👋

I'm **Nathan**, the Founder of Venusnap. I just wanted to personally say thank you for joining us!

We're building something special a creative platform where people like you can express themselves, connect with others, and even earn from what they love.

As a warm welcome, we’ve added **free points** to your account which you can use for:
- Generating templates
- Creating ads
- And unlocking early creative tools

Make the most of them and let your imagination run wild!

@component('mail::panel')
If you ever have feedback, ideas, or need help, feel free to reach out. We're here for you.
@endcomponent

Thanks again for joining Venusnap. We’re thrilled to have you on board. 🎉

Warm regards,
**Nathan Mwamba**
Founder – Venusnap
[venusnap.com](https://www.venusnap.com)

---

*P.S. We've logged your successful registration from **{{ $deviceinfo }}** (IP: **{{ $ipaddress }}**).*
*Your security is important to us — this is just for your awareness.*
@endcomponent
