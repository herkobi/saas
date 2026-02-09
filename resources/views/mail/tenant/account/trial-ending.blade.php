@component('mail::message')
# Deneme Süresi Bitiyor

Merhaba {{ $user->name }},

**{{ $planName }}** planı deneme sürenizin bitmesine **{{ $daysRemaining }} gün** kaldı.

@component('mail::panel')
**Plan:** {{ $planName }}<br>
**Deneme Bitiş Tarihi:** {{ $trialEndsAt }}
@endcomponent

Deneme süreniz sona erdikten sonra hizmetlerimizden yararlanmaya devam etmek için bir abonelik planı seçmeniz gerekmektedir.

@component('mail::button', ['url' => $subscriptionUrl])
Abonelik Planlarını İncele
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
