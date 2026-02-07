@component('mail::message')
# Reset your password

We received a request to reset your password for **{{ $appName }}**.

@component('mail::button', ['url' => $url])
Reset Password
@endcomponent

This link will expire in **{{ $expire }} minutes**.
If you did not request a password reset, you can safely ignore this email.

Thanks,  
{{ $appName }}
@endcomponent
