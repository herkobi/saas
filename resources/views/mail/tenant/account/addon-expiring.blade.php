@component('mail::message')
# Eklenti Süresi Dolmak Üzere

Merhaba {{ $user->name }},

**{{ $addonName }}** eklentinizin süresinin dolmasına **{{ $daysRemaining }} gün** kaldı.

@component('mail::panel')
**Eklenti:** {{ $addonName }}<br>
**Tür:** {{ $addonType }}<br>
**Özellik:** {{ $featureName }}<br>
**Adet:** {{ $quantity }}<br>
**Bitiş Tarihi:** {{ $expiresAt }}
@endcomponent

Eklentinizi yenilemek için aşağıdaki butona tıklayabilirsiniz.

@component('mail::button', ['url' => $renewUrl])
Eklentiyi Yenile
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
