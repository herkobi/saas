@component('mail::message')
# Eklenti Satın Alma Onayı

Merhaba {{ $user->name }},

Eklenti satın alımınız başarıyla tamamlandı.

@component('mail::panel')
**Eklenti:** {{ $addonName }}<br>
**Tür:** {{ $addonType }}<br>
**Özellik:** {{ $featureName }}<br>
**Adet:** {{ $quantity }}<br>
**Toplam Tutar:** {{ $totalPrice }} {{ $currency }}<br>
**Başlangıç:** {{ $startedAt }}
@if ($expiresAt)
<br>**Bitiş:** {{ $expiresAt }}
@endif
@if ($isRecurring)
<br>**Otomatik Yenileme:** Evet
@endif
@endcomponent

@component('mail::button', ['url' => $addonsUrl])
Eklentilerimi Görüntüle
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
