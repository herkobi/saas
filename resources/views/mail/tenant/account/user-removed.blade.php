@component('mail::message')
# Hesaptan Çıkarıldınız

Merhaba {{ $user->name }},

**{{ $tenantName }}** hesabından çıkarıldınız. Bu hesaba artık erişiminiz bulunmamaktadır.

Bunun bir hata olduğunu düşünüyorsanız, lütfen hesap yöneticisi ile iletişime geçin.

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
