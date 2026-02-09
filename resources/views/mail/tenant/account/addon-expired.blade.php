@component('mail::message')
# Eklenti Süresi Doldu

Merhaba {{ $user->name }},

**{{ $addonName }}** eklentinizin süresi doldu.

@component('mail::panel')
**Eklenti:** {{ $addonName }}<br>
**Tür:** {{ $addonType }}<br>
**Özellik:** {{ $featureName }}<br>
**Adet:** {{ $quantity }}<br>
**Sona Erme Tarihi:** {{ $expiredAt }}
@endcomponent

Eklentiyi yeniden satın almak için aşağıdaki butona tıklayabilirsiniz.

@component('mail::button', ['url' => $renewUrl])
Eklentiyi Yenile
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
