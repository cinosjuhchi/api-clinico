@component('mail::message')
# Sahkan Alamat Emel Anda

Hai {{ $data['name'] }},

Terima kasih kerana mendaftar di aplikasi kami. Sila klik butang di bawah ini untuk mengesahkan alamat emel anda.

@component('mail::button', ['url' => $data['verification_url']])
Sahkan Emel
@endcomponent

Jika anda tidak mendaftar untuk akaun ini, tidak perlu tindakan lanjut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
