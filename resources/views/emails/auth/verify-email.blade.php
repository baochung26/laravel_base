@component('mail::message')
# Verify your email address

Welcome to **{{ $appName }}**! Please confirm your email address to activate your account.

@component('mail::button', ['url' => $url])
Verify Email
@endcomponent

If you did not create an account, no further action is required.

Thanks,  
{{ $appName }}
@endcomponent
