<x-mail::message>
# Password Reset Code

Hello Buddy,

You recently requested to reset your password. Use the following code to continue:

<x-mail::panel>
## {{ $code }}
</x-mail::panel>

This code will expire in 15 minutes. If you did not request this, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
