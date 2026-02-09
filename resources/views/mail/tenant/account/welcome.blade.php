@component('mail::message')
# Hoş Geldiniz!

Merhaba {{ $user->name }},

**{{ $tenantName }}** hesabınız başarıyla oluşturuldu. Artık platformumuzu kullanmaya başlayabilirsiniz.

@component('mail::button', ['url' => $dashboardUrl])
Panele Git
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
