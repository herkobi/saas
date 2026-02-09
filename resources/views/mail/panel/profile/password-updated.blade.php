@component('mail::message')
# Şifreniz Değiştirildi

Merhaba {{ $user->name }},

Yönetim paneli hesabınızın şifresi **{{ $changedAt }}** tarihinde değiştirildi.

@component('mail::panel')
**IP Adresi:** {{ $ipAddress }}<br>
**Tarayıcı:** {{ $userAgent }}
@endcomponent

Bu işlemi siz yapmadıysanız, lütfen derhal şifrenizi değiştirin ve hesabınızın güvenliğini kontrol edin.

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
