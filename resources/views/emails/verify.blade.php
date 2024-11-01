@component('mail::message')
# Verify Your Email Address

Hi {{ $data['name'] }},

Thank you for registering on our application. Please click the button below to verify your email address.

@component('mail::button', ['url' => $data['verification_url']])
Verify Email
@endcomponent

If you did not register for this account, no further action is required.

Thank you,<br>
{{ config('app.name') }}
@endcomponent
