@component('mail::message')
# Deneme Süresi Sona Erdi

Merhaba {{ $user->name }},

**{{ $planName }}** planı deneme süreniz **{{ $trialEndsAt }}** tarihinde sona erdi.

Hizmetlerimizden yararlanmaya devam etmek için bir abonelik planı seçmeniz gerekmektedir.

@component('mail::button', ['url' => $subscriptionUrl])
Abonelik Planlarını İncele
@endcomponent

Saygılarımızla,<br>
{{ settings('site_name') ?? config('app.name') }}
@endcomponent
