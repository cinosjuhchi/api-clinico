<x-mail::message>
# Reset Password

Halo {{ $data['email'] }},

Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.

<x-mail::button :url="$data['resetLink']">
Reset Password
</x-mail::button>

Jika Anda tidak meminta reset password, abaikan email ini.

Tautan reset password ini akan kedaluwarsa dalam 1 jam.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>